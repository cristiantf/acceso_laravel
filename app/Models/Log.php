<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'company_id',
        'fecha',
        'usuario_id',
        'tipo_evento',
        'origen',
        'latitud',
        'longitud',
        'descripcion',
        'foto_path',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ========== RELACIONES ==========

    /**
     * Relación con la empresa
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'biometric_id');
    }

    // ========== SCOPES ==========

    /**
     * Filtrar logs de una compañía específica
     */
    public function scopeOfCompany($query, $company = null)
    {
        $company = $company ?? app('company');
        return $query->where('company_id', $company->id);
    }

    /**
     * Filtrar logs de los últimos N días
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

