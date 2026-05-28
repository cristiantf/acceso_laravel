<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use App\Models\Comando;
use App\Models\Permiso;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        $docentes = User::where('rol', 'docente')->get();
        return view('admin', compact('docentes'));
    }

    public function abrirPuerta()
    {
        Comando::create(['instruccion' => 'ABRIR']);
        Log::create([
            'fecha' => now('America/Guayaquil'),
            'usuario_id' => auth()->user()->biometric_id,
            'tipo_evento' => 'Apertura Remota',
            'origen' => 'Panel Control'
        ]);
        return back()->with('success', 'Comando de apertura enviado.');
    }

    public function sincronizarHora(Request $request)
    {
        $timeStr = $request->input('new_time');
        if ($timeStr) {
            try {
                $dtObj = Carbon::parse($timeStr);
                $isoTime = $dtObj->format('Y-m-d\TH:i:sP');
                Comando::create(['instruccion' => "SET_TIME|{$isoTime}"]);
                return back()->with('success', "Comando de sincronización ({$isoTime}) enviado.");
            } catch (\Exception $e) {
                return back()->with('error', 'Formato inválido.');
            }
        }
        return back();
    }

    public function gestionAsistencia(Request $request)
    {
        $query = Log::with('user')->orderBy('fecha', 'desc');

        if ($request->fecha_inicio) {
            $query->whereDate('fecha', '>=', $request->fecha_inicio);
        }
        if ($request->fecha_fin) {
            $query->whereDate('fecha', '<=', $request->fecha_fin);
        }
        if ($request->docente_id && $request->docente_id != 'todos') {
            $user = User::find($request->docente_id);
            if ($user) $query->where('usuario_id', $user->biometric_id);
        }

        $logs_data = $query->paginate(20);
        $docentes = User::where('rol', 'docente')->get();

        $filtros = new \stdClass();
        $filtros->fecha_inicio = $request->fecha_inicio ?? '';
        $filtros->fecha_fin = $request->fecha_fin ?? '';
        $filtros->docente_id = $request->docente_id ?? 'todos';
        return view('gestion_asistencia', compact('logs_data', 'docentes', 'filtros'));
    }

    public function getLogsJson()
    {
        $logs = Log::with('user')->orderBy('id', 'desc')->take(20)->get()->map(function($log) {
            return [
                'fecha' => Carbon::parse($log->fecha)->format('Y-m-d H:i:s'),
                'nombre' => $log->user ? $log->user->nombre : 'ID Huella: ' . $log->usuario_id,
                'tipo_evento' => $log->tipo_evento,
                'origen' => $log->origen,
                'lat' => $log->latitud,
                'lon' => $log->longitud,
                'foto' => $log->foto_path ? asset('storage/uploads/' . $log->foto_path) : null,
                'desc' => $log->descripcion
            ];
        });

        $queue_len = Comando::where('estado', 'PENDIENTE')->count();

        return response()->json(['logs' => $logs, 'queue_length' => $queue_len]);
    }

    public function gestionPermisos(Request $request)
    {
        $query = Permiso::with('docente')->orderBy('fecha_permiso', 'desc');

        if ($request->fecha_inicio) {
            $query->whereDate('fecha_permiso', '>=', $request->fecha_inicio);
        }
        if ($request->fecha_fin) {
            $query->whereDate('fecha_permiso', '<=', $request->fecha_fin);
        }
        if ($request->docente_id && $request->docente_id != 'todos') {
            $query->where('user_id', $request->docente_id);
        }

        $permisos = $query->paginate(20);
        $docentes = User::where('rol', 'docente')->get();

        $filtros = new \stdClass();
        $filtros->fecha_inicio = $request->fecha_inicio ?? '';
        $filtros->fecha_fin = $request->fecha_fin ?? '';
        $filtros->docente_id = $request->docente_id ?? 'todos';
        return view('gestion_permisos', compact('permisos', 'docentes', 'filtros'));
    }

    public function editarAsistencia($id)
    {
        $log = Log::findOrFail($id);
        $docentes = User::where('rol', 'docente')->get();
        $log_user = User::where('biometric_id', $log->usuario_id)->first();
        return view('editar_asistencia', compact('log', 'docentes', 'log_user'));
    }

    public function actualizarAsistencia(Request $request)
    {
        $validated = $request->validate([
            'log_id' => 'required|exists:logs,id',
            'docente_id' => 'required|exists:usuarios,id',
            'fecha' => 'required|date_format:Y-m-d\TH:i',
            'tipo_evento' => 'required|string|max:50|in:ENTRADA,SALIDA,ASISTENCIA_WEB,APERTURA_REMOTA',
            'origen' => 'required|string|max:50|in:dispositivo,web,manual',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'log_id.required' => 'ID de registro requerido',
            'log_id.exists' => 'El registro no existe',
            'docente_id.required' => 'Debe seleccionar un docente',
            'docente_id.exists' => 'El docente seleccionado no existe',
            'fecha.required' => 'La fecha es requerida',
            'fecha.date_format' => 'Formato de fecha inválido',
            'tipo_evento.in' => 'Tipo de evento inválido',
            'origen.in' => 'Origen inválido',
            'descripcion.max' => 'La descripción no debe exceder 500 caracteres',
        ]);

        try {
            $log = Log::findOrFail($validated['log_id']);
            $docente = User::findOrFail($validated['docente_id']);

            // Validar que el docente pertenece a la empresa
            if ($docente->company_id !== auth()->user()->company_id) {
                return back()->withErrors(['docente_id' => 'El docente no pertenece a esta empresa']);
            }

            // Actualizar log
            $log->usuario_id = $docente->biometric_id;
            $log->fecha = Carbon::parse($validated['fecha'])->setTimezone('America/Guayaquil');
            $log->tipo_evento = $validated['tipo_evento'];
            $log->origen = $validated['origen'];
            $log->descripcion = $validated['descripcion'];
            $log->save();

            return back()->with('success', 'Asistencia actualizada correctamente');
        } catch (\Exception $e) {
            \Log::error('Error actualizando asistencia: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function eliminarAsistencia($id)
    {
        Log::findOrFail($id)->delete();
        return redirect()->route('gestion_asistencia')->with('success', 'Registro eliminado.');
    }

    public function editarPermiso($id)
    {
        $permiso = Permiso::findOrFail($id);
        $docentes = User::where('rol', 'docente')->get();
        return view('editar_permiso', compact('permiso', 'docentes'));
    }

    public function eliminarPermiso($id)
    {
        Permiso::findOrFail($id)->delete();
        return redirect()->route('gestion_permisos')->with('success', 'Permiso eliminado.');
    }

    public function editarDocente($id)
    {
        $docente = User::findOrFail($id);
        return view('editar_docente', compact('docente'));
    }

    public function eliminarDocente($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin_dashboard')->with('success', 'Docente eliminado.');
    }

    public function crearDocente(Request $request)
    {
        $validated = $request->validate([
            'biometric_id' => [
                'required',
                'integer',
                'min:1',
                'max:9999999',
                'unique:usuarios,biometric_id',
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
                'min:3',
                'regex:/^[\pL\s\-\'áéíóúñü]+$/u',
            ],
            'username' => [
                'required',
                'string',
                'max:100',
                'min:4',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'unique:usuarios,username',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'acceso_puerta' => 'nullable|boolean',
        ], [
            'biometric_id.required' => 'ID Biométrico requerido',
            'biometric_id.unique' => 'Este ID biométrico ya está registrado',
            'nombre.regex' => 'El nombre solo puede contener letras, espacios y guiones',
            'username.regex' => 'Username solo acepta letras, números, puntos, guiones y guiones bajos',
            'username.unique' => 'Este username ya está en uso',
            'password.confirmed' => 'Las contraseñas no coinciden',
            'password.regex' => 'Password debe tener mayúsculas, minúsculas y números',
            'password.min' => 'Password debe tener mínimo 8 caracteres',
        ]);

        try {
            $docente = new \App\Models\User();
            $docente->company_id = auth()->user()->company_id;
            $docente->biometric_id = $validated['biometric_id'];
            $docente->nombre = trim($validated['nombre']);
            $docente->username = strtolower($validated['username']);
            $docente->password = Hash::make($validated['password']);
            $docente->rol = 'docente';
            $docente->acceso_puerta = $validated['acceso_puerta'] ? 1 : 0;
            $docente->save();

            return back()->with('success', "Docente '{$docente->nombre}' creado correctamente");
        } catch (\Exception $e) {
            \Log::error('Error creando docente: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear docente: ' . $e->getMessage()]);
        }
    }

    public function actualizarDocente(Request $request)
    {
        $docente = User::findOrFail($request->user_id);

        // Validar que el docente pertenece a la empresa del admin
        if ($docente->company_id !== auth()->user()->company_id) {
            return back()->withErrors(['error' => 'No tienes permisos para modificar este docente']);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:usuarios,id',
            'nombre' => [
                'required',
                'string',
                'max:150',
                'min:3',
                'regex:/^[\pL\s\-\'áéíóúñü]+$/u',
            ],
            'bio_id' => [
                'required',
                'integer',
                'min:1',
                'max:9999999',
                'unique:usuarios,biometric_id,' . $docente->id,
            ],
            'username' => [
                'required',
                'string',
                'max:100',
                'min:4',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'unique:usuarios,username,' . $docente->id,
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
            'acceso_puerta' => 'nullable|boolean',
        ], [
            'nombre.regex' => 'El nombre solo puede contener letras, espacios y guiones',
            'username.regex' => 'Username solo acepta letras, números, puntos, guiones y guiones bajos',
            'password.regex' => 'Password debe tener mayúsculas, minúsculas y números',
        ]);

        try {
            $docente->nombre = trim($validated['nombre']);
            $docente->biometric_id = $validated['bio_id'];
            $docente->username = strtolower($validated['username']);
            
            if ($request->filled('password')) {
                $docente->password = Hash::make($validated['password']);
            }
            
            $docente->acceso_puerta = $validated['acceso_puerta'] ? 1 : 0;
            $docente->save();

            return back()->with('success', "Docente '{$docente->nombre}' actualizado correctamente");
        } catch (\Exception $e) {
            \Log::error('Error actualizando docente: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function crearPermiso(Request $request)
    {
        $validated = $request->validate([
            'docente_id' => 'required|exists:usuarios,id',
            'fecha_permiso' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'tipo' => 'nullable|in:licencia,comisión,permiso',
            'observacion' => 'nullable|string|max:500',
        ], [
            'docente_id.required' => 'Debe seleccionar un docente',
            'docente_id.exists' => 'El docente seleccionado no existe',
            'fecha_permiso.after_or_equal' => 'La fecha no puede ser en el pasado',
            'fecha_permiso.date_format' => 'Formato de fecha inválido',
            'observacion.max' => 'La observación no debe exceder 500 caracteres',
        ]);

        try {
            $docente = User::findOrFail($validated['docente_id']);

            // Validar que el docente pertenece a la empresa
            if ($docente->company_id !== auth()->user()->company_id) {
                return back()->withErrors(['docente_id' => 'El docente no pertenece a esta empresa']);
            }

            // Verificar que no exista permiso solapado
            $permisoExistente = Permiso::where('user_id', $docente->id)
                ->whereDate('fecha_permiso', $validated['fecha_permiso'])
                ->where('deleted_at', null)
                ->exists();

            if ($permisoExistente) {
                return back()->withErrors(['fecha_permiso' => 'Ya existe un permiso para esta fecha']);
            }

            $permiso = new Permiso();
            $permiso->company_id = auth()->user()->company_id;
            $permiso->user_id = $validated['docente_id'];
            $permiso->fecha_permiso = $validated['fecha_permiso'];
            $permiso->tipo = $validated['tipo'] ?? 'permiso';
            $permiso->observacion = $validated['observacion'];
            $permiso->save();

            return back()->with('success', "Permiso creado para {$docente->nombre}");
        } catch (\Exception $e) {
            \Log::error('Error creando permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear permiso: ' . $e->getMessage()]);
        }
    }

    public function actualizarPermiso(Request $request)
    {
        $validated = $request->validate([
            'permiso_id' => 'required|exists:permisos,id',
            'docente_id' => 'required|exists:usuarios,id',
            'fecha_permiso' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
            ],
            'tipo' => 'nullable|in:licencia,comisión,permiso',
            'observacion' => 'nullable|string|max:500',
        ], [
            'permiso_id.required' => 'ID de permiso requerido',
            'permiso_id.exists' => 'El permiso no existe',
            'docente_id.exists' => 'El docente no existe',
            'fecha_permiso.after_or_equal' => 'La fecha no puede ser en el pasado',
        ]);

        try {
            $permiso = Permiso::findOrFail($validated['permiso_id']);
            $docente = User::findOrFail($validated['docente_id']);

            // Validar pertenencia a empresa
            if ($docente->company_id !== auth()->user()->company_id || $permiso->company_id !== auth()->user()->company_id) {
                return back()->withErrors(['error' => 'No tienes permisos para modificar este permiso']);
            }

            // Verificar solapamiento (excepto el permiso actual)
            $permisoSolapado = Permiso::where('user_id', $docente->id)
                ->whereDate('fecha_permiso', $validated['fecha_permiso'])
                ->where('id', '!=', $permiso->id)
                ->where('deleted_at', null)
                ->exists();

            if ($permisoSolapado) {
                return back()->withErrors(['fecha_permiso' => 'Ya existe otro permiso para esta fecha']);
            }

            $permiso->user_id = $validated['docente_id'];
            $permiso->fecha_permiso = $validated['fecha_permiso'];
            $permiso->tipo = $validated['tipo'] ?? $permiso->tipo;
            $permiso->observacion = $validated['observacion'];
            $permiso->save();

            return back()->with('success', 'Permiso actualizado correctamente');
        } catch (\Exception $e) {
            \Log::error('Error actualizando permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()]);
        }
    }

    public function descargarReporteMatricial(Request $request)
    {
        $export = new \App\Exports\AsistenciaExport(
            $request->fecha_inicio,
            $request->fecha_fin,
            $request->docente_id,
            $request->hora_inicio_m,
            $request->hora_fin_m,
            $request->hora_inicio_t,
            $request->hora_fin_t
        );

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'reporte_asistencia_' . date('Ymd_His') . '.xlsx');
    }

    public function descargarReportePermisos(Request $request)
    {
        $export = new \App\Exports\PermisosExport(
            $request->fecha_inicio_permiso,
            $request->fecha_fin_permiso,
            $request->docente_id_permiso
        );

        return \Maatwebsite\Excel\Facades\Excel::download($export, 'reporte_permisos_' . date('Ymd_His') . '.xlsx');
    }
}
