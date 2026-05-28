<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBranding extends Model
{
    protected $fillable = [
        'company_id',
        'logo_path',
        'favicon_path',
        'login_background_path',
        'colores',
        'textos',
        'tema',
        'mostrar_marca_agua',
        'mostrar_logo_navbar',
        'fuente_personalizada',
        'mostrar_footer',
        'url_soporte',
        'url_terminos',
        'url_privacidad',
    ];

    protected $casts = [
        'colores' => 'array',
        'textos' => 'array',
        'mostrar_marca_agua' => 'boolean',
        'mostrar_logo_navbar' => 'boolean',
        'mostrar_footer' => 'boolean',
    ];

    // ========== RELACIONES ==========

    /**
     * Empresa a la que pertenece este branding
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // ========== MÉTODOS ÚTILES ==========

    /**
     * Obtener un color específico
     */
    public function color(string $key): ?string
    {
        return $this->colores[$key] ?? null;
    }

    /**
     * Obtener un texto específico
     */
    public function texto(string $key): ?string
    {
        return $this->textos[$key] ?? null;
    }

    /**
     * Obtener URL del logo
     */
    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset("storage/company-logos/{$this->logo_path}") : null;
    }

    /**
     * Obtener URL del favicon
     */
    public function faviconUrl(): ?string
    {
        return $this->favicon_path ? asset("storage/company-favicons/{$this->favicon_path}") : null;
    }

    /**
     * Obtener URL del fondo de login
     */
    public function loginBackgroundUrl(): ?string
    {
        return $this->login_background_path ? asset("storage/company-backgrounds/{$this->login_background_path}") : null;
    }

    /**
     * Generar CSS dinámico basado en los colores y tema
     */
    public function generarCSS(): string
    {
        $colores = $this->colores;

        return ":root {
            --primary-color: {$colores['primario']};
            --secondary-color: {$colores['secundario']};
            --accent-color: {$colores['acento']};
            --login-bg: {$colores['fondo_login']};
            --text-main: {$colores['texto_principal']};
            --sidebar-bg: {$colores['barra_lateral']};
            --navbar-bg: {$colores['barra_navegacion']};
            --btn-primary: {$colores['boton_primario']};
            --btn-hover: {$colores['boton_hover']};
        }";
    }
}

