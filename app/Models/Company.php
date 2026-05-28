<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'email',
        'descripcion',
        'subdomain',
        'plan_tipo',
        'fecha_inicio_suscripcion',
        'fecha_fin_suscripcion',
        'activa',
        'limite_usuarios',
        'zona_horaria',
        'idioma',
        'caracteristicas_habilitadas',
    ];

    protected $casts = [
        'activa' => 'boolean',
        'fecha_inicio_suscripcion' => 'date',
        'fecha_fin_suscripcion' => 'date',
        'caracteristicas_habilitadas' => 'array',
    ];

    // ========== RELACIONES ==========

    /**
     * Usuarios de esta empresa
     */
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Configuración de marca de esta empresa
     */
    public function branding()
    {
        return $this->hasOne(CompanyBranding::class);
    }

    /**
     * Logs de esta empresa
     */
    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Permisos de esta empresa
     */
    public function permisos()
    {
        return $this->hasMany(Permiso::class);
    }

    /**
     * Comandos de esta empresa
     */
    public function comandos()
    {
        return $this->hasMany(Comando::class);
    }

    // ========== MÉTODOS ÚTILES ==========

    /**
     * Verificar si la suscripción está activa
     */
    public function suscripcionActiva(): bool
    {
        return $this->activa && now() <= $this->fecha_fin_suscripcion;
    }

    /**
     * Obtener usuarios restantes permitidos
     */
    public function usuariosRestantes(): int
    {
        return $this->limite_usuarios - $this->usuarios()->count();
    }

    /**
     * Verificar si puede agregar un nuevo usuario
     */
    public function puedeAgregarUsuario(): bool
    {
        return $this->usuariosRestantes() > 0;
    }

    /**
     * Obtener días restantes de suscripción
     */
    public function diasRestantes(): int
    {
        return now()->diffInDays($this->fecha_fin_suscripcion, false);
    }

    /**
     * Verificar si tiene una característica habilitada
     */
    public function tieneCaracteristica(string $caracteristica): bool
    {
        return in_array($caracteristica, $this->caracteristicas_habilitadas ?? []);
    }
}

