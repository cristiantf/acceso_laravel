<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'fecha_permiso',
        'observacion',
    ];

    protected $casts = [
        'fecha_permiso' => 'date',
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
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Alias para la relación con el usuario (docente)
     */
    public function docente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ========== SCOPES ==========

    /**
     * Filtrar permisos de una compañía específica
     */
    public function scopeOfCompany($query, $company = null)
    {
        $company = $company ?? app('company');
        return $query->where('company_id', $company->id);
    }

    /**
     * Filtrar permisos activos (actuales o futuros)
     */
    public function scopeActive($query)
    {
        return $query->where('fecha_permiso', '>=', now()->toDateString());
    }

    /**
     * Filtrar permisos por rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('fecha_permiso', [$startDate, $endDate]);
    }
}

