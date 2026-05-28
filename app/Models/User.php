<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'biometric_id',
        'nombre',
        'username',
        'password',
        'rol',
        'acceso_puerta',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ========== RELACIONES ==========

    /**
     * Relación con la empresa/compañía
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ========== SCOPES ==========

    /**
     * Filtrar usuarios de una compañía específica
     */
    public function scopeOfCompany($query, $company = null)
    {
        $company = $company ?? app('company');
        return $query->where('company_id', $company->id);
    }

    /**
     * Filtrar usuarios activos
     */
    public function scopeActive($query)
    {
        return $query->where('activo', true);
    }
}
