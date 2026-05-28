<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyBranding;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    public function getBranding(Company $company): CompanyBranding
    {
        return $company->branding ?? $this->crearBrandingDefecto($company);
    }

    public function crearBrandingDefecto(Company $company): CompanyBranding
    {
        return CompanyBranding::create([
            'company_id' => $company->id,
            'colores' => [
                'primario' => '#0d6efd',
                'secundario' => '#6c757d',
                'acento' => '#198754',
                'fondo_login' => '#f0f2f5',
                'texto_principal' => '#212529',
                'barra_lateral' => '#1a1d20',
                'barra_navegacion' => '#212529',
                'boton_primario' => '#0d6efd',
                'boton_hover' => '#0b5ed7',
            ],
            'textos' => [
                'nombre_sistema' => 'Sistema ISTAE',
                'subtitulo' => 'Control Biométrico de Acceso',
                'nombre_empresa' => $company->nombre,
                'pie_pagina' => "© 2026 {$company->nombre}",
                'mensaje_bienvenida' => "Bienvenido a {$company->nombre}",
                'slogan' => 'Seguridad y Control a tu alcance',
            ],
            'tema' => 'light'
        ]);
    }

    public function guardarBranding(Company $company, array $datos): CompanyBranding
    {
        $branding = $this->getBranding($company);

        if (isset($datos['colores'])) {
            $branding->colores = array_merge($branding->colores, $datos['colores']);
        }

        if (isset($datos['textos'])) {
            $branding->textos = array_merge($branding->textos, $datos['textos']);
        }

        if (isset($datos['tema'])) {
            $branding->tema = $datos['tema'];
        }

        $branding->save();

        return $branding;
    }

    public function subirLogo(Company $company, $archivo)
    {
        // Validar archivo
        if (!$archivo->isValid()) {
            throw new \Exception('Archivo inválido o corrupto');
        }

        // Obtener extensión
        $ext = $archivo->getClientOriginalExtension();
        $nombre = "logo-{$company->id}-" . time() . ".{$ext}";
        
        // Guardar en storage
        $path = $archivo->storeAs('company-logos', $nombre, 'public');
        
        // Obtener branding y actualizar
        $branding = $this->getBranding($company);
        
        // Eliminar logo anterior si existe
        if ($branding->logo_path && \Storage::disk('public')->exists("company-logos/{$branding->logo_path}")) {
            \Storage::disk('public')->delete("company-logos/{$branding->logo_path}");
        }
        
        $branding->logo_path = $nombre;
        $branding->save();

        return $branding;
    }

    /**
     * Validar que un color en formato hex es válido
     */
    public function validarColor(string $color): bool
    {
        return preg_match('/#[a-f0-9]{6}$/i', $color) === 1;
    }

    /**
     * Limpiar branding: remover colores inválidos
     */
    public function limpiarColores(array $colores): array
    {
        return array_filter($colores, fn($color) => $this->validarColor($color));
    }
}