<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use App\Models\Comando;

class DocenteController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $logs = Log::where('usuario_id', $user->biometric_id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return view('docente', compact('logs'));
    }

    public function abrirPuerta()
    {
        $user = Auth::user();
        if ($user->acceso_puerta == 1) {
            Comando::create(['instruccion' => 'ABRIR']);
            Log::create([
                'fecha' => now('America/Guayaquil'),
                'usuario_id' => $user->biometric_id,
                'tipo_evento' => 'Apertura Remota',
                'origen' => 'Asistencia remota'
            ]);
            return back()->with('success', 'Comando de apertura enviado.');
        }
        return back()->with('error', 'No tienes permisos.');
    }

    public function marcarWeb(Request $request)
    {
        $user = Auth::user();
        $lat = $request->input('latitud');
        $lon = $request->input('longitud');
        $descripcion = $request->input('descripcion');
        
        if (!$lat || !$lon) {
            return back()->with('error', 'Ubicación requerida.');
        }

        $filename = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = $user->id . '_' . now()->format('YmdHis') . '_' . $file->getClientOriginalName();
            $file->storeAs('uploads', $filename, 'public');
        }

        Log::create([
            'fecha' => now('America/Guayaquil'),
            'usuario_id' => $user->biometric_id,
            'tipo_evento' => 'Asistencia',
            'origen' => 'Asistencia remota',
            'latitud' => $lat,
            'longitud' => $lon,
            'descripcion' => $descripcion,
            'foto_path' => $filename
        ]);

        return back()->with('success', 'Asistencia remota registrada con éxito.');
    }
}
