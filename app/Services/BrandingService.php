<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyBranding;

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
        $path = $archivo->store('company-logos', 'public');
        $branding = $this->getBranding($company);
        $branding->logo_path = basename($path);
        $branding->save();

        return $branding;
    }
}