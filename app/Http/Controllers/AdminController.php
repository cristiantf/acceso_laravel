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
        // Placeholder for logic
        return redirect()->route('gestion_asistencia')->with('success', 'Asistencia actualizada.');
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
        // Validar los datos recibidos
        $request->validate([
            'biometric_id' => 'required|integer|unique:usuarios,biometric_id',
            'nombre' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:usuarios,username',
            'password' => 'required|string|min:6',
            'acceso_puerta' => 'nullable|boolean',
        ]);

        // Crear el docente
        $docente = new \App\Models\User();
        $docente->biometric_id = $request->biometric_id;
        $docente->nombre = $request->nombre;
        $docente->username = $request->username;
        $docente->password = bcrypt($request->password);
        $docente->rol = 'docente';
        $docente->acceso_puerta = $request->has('acceso_puerta') ? 1 : 0;
        $docente->save();

        return redirect()->route('admin_dashboard')->with('success', 'Docente creado.');
    }

    public function actualizarDocente(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'bio_id' => 'required|integer',
            'username' => 'required|string|max:255',
        ]);

        $docente = User::findOrFail($request->user_id);
        $docente->nombre = $request->nombre;
        $docente->biometric_id = $request->bio_id;
        $docente->username = $request->username;
        if ($request->filled('password')) {
            $docente->password = bcrypt($request->password);
        }
        $docente->acceso_puerta = $request->has('acceso_puerta') ? 1 : 0;
        $docente->save();

        return redirect()->route('admin_dashboard')->with('success', 'Docente actualizado.');
    }

    public function crearPermiso(Request $request)
    {
        $request->validate([
            'docente_id' => 'required|exists:usuarios,id',
            'fecha_permiso' => 'required|date',
        ]);

        $permiso = new Permiso();
        $permiso->user_id = $request->docente_id;
        $permiso->fecha_permiso = $request->fecha_permiso;
        $permiso->observacion = $request->observacion;
        $permiso->save();

        return redirect()->route('gestion_permisos')->with('success', 'Permiso creado.');
    }

    public function actualizarPermiso(Request $request)
    {
        $request->validate([
            'permiso_id' => 'required|exists:permisos,id',
            'docente_id' => 'required|exists:usuarios,id',
            'fecha_permiso' => 'required|date',
        ]);

        $permiso = Permiso::findOrFail($request->permiso_id);
        $permiso->user_id = $request->docente_id;
        $permiso->fecha_permiso = $request->fecha_permiso;
        $permiso->observacion = $request->observacion;
        $permiso->save();

        return redirect()->route('gestion_permisos')->with('success', 'Permiso actualizado.');
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
