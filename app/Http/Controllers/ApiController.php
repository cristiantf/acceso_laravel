<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Log;
use App\Models\Comando;
use Carbon\Carbon;

class ApiController extends Controller
{
    // Token del NodeMCU (esto debería ir en el .env, pero por ahora lo dejamos aquí para simplificar)
    private $tokenNode = 'istae1805A';

    public function sincronizar()
    {
        $usuarios = User::where('acceso_puerta', 1)->get();
        $ids = $usuarios->pluck('biometric_id')->implode(',');
        return response($ids);
    }

    public function recibirLog(Request $request)
    {
        if (!$request->has('token') || $request->input('token') !== $this->tokenNode) {
            return response()->json(['status' => 'error', 'message' => 'Token inválido'], 403);
        }

        $fechaLog = null;
        $fechaStr = $request->input('fecha_dispositivo');

        if ($fechaStr) {
            try {
                $fechaLimpia = str_replace('T', ' ', $fechaStr);
                $fechaLog = Carbon::createFromFormat('Y-m-d H:i:s', substr($fechaLimpia, 0, 19));
            } catch (\Exception $e) {
                $fechaLog = null;
            }
        }

        if (!$fechaLog) {
            $fechaLog = now('America/Guayaquil');
        }

        Log::create([
            'fecha' => $fechaLog,
            'usuario_id' => $request->input('id'),
            'tipo_evento' => 'Asistencia + puerta',
            'origen' => 'Huella'
        ]);

        return response()->json(['status' => 'success']);
    }

    public function checkComando()
    {
        $cmd = Comando::where('estado', 'PENDIENTE')->orderBy('id', 'asc')->first();
        if ($cmd) {
            $cmd->estado = 'ENVIADO';
            $cmd->save();
            return response($cmd->instruccion);
        }
        return response('NADA');
    }
}
