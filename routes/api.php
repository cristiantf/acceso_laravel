<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/sincronizar', [ApiController::class, 'sincronizar']);
Route::post('/recibir_log', [ApiController::class, 'recibirLog']);
Route::get('/check_comando', [ApiController::class, 'checkComando']);
