<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrandingController extends Controller
{
    private BrandingService $brandingService;

    public function __construct(BrandingService $brandingService)
    {
        $this->brandingService = $brandingService;
    }

    public function show()
    {
        $company = auth()->user()->company;
        $branding = $this->brandingService->getBranding($company);

        return view('admin.branding.show', [
            'company' => $company,
            'branding' => $branding,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nombre_sistema' => 'nullable|string|max:100|min:3',
            'subtitulo' => 'nullable|string|max:200|min:3',
            'nombre_empresa' => 'nullable|string|max:150|min:2',
            'pie_pagina' => 'nullable|string|max:300|min:5',
            'slogan' => 'nullable|string|max:150|min:3',
            'email_soporte' => 'nullable|email|max:100',
            'telefono_soporte' => 'nullable|string|max:20',
            'colores.primario' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.secundario' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.acento' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.navbar' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.botones' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.texto' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.barra_lateral' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.fondo' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'colores.error' => 'nullable|regex:/#[a-f0-9]{6}/i',
            'tema' => 'in:light,dark,custom',
            'mostrar_marca_agua' => 'boolean',
            'mostrar_logo_navbar' => 'boolean',
            'mostrar_footer' => 'boolean',
        ], [
            'nombre_sistema.min' => 'El nombre del sistema debe tener al menos 3 caracteres',
            'colores.*.regex' => 'Los colores deben estar en formato hexadecimal válido (#XXXXXX)',
            'email_soporte.email' => 'El email de soporte debe ser válido',
        ]);

        $company = auth()->user()->company;
        
        // Extraer y validar colores
        $colores = [];
        if (isset($validated['colores']) && is_array($validated['colores'])) {
            foreach ($validated['colores'] as $colorKey => $value) {
                if ($value && $this->brandingService->validarColor($value)) {
                    $colores[$colorKey] = $value;
                }
            }
        }

        // Preparar textos
        $textFields = ['nombre_sistema', 'subtitulo', 'nombre_empresa', 'pie_pagina', 'slogan', 'email_soporte', 'telefono_soporte'];
        $textos = [];
        foreach ($textFields as $field) {
            if (array_key_exists($field, $validated)) {
                $textos[$field] = $validated[$field];
            }
        }

        try {
            $this->brandingService->guardarBranding($company, [
                'colores' => $colores,
                'textos' => $textos,
                'tema' => $validated['tema'] ?? 'light',
                'mostrar_marca_agua' => $validated['mostrar_marca_agua'] ?? false,
                'mostrar_logo_navbar' => $validated['mostrar_logo_navbar'] ?? false,
                'mostrar_footer' => $validated['mostrar_footer'] ?? false,
            ]);

            return back()->with('success', 'Branding actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error actualizando branding: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar branding: ' . $e->getMessage()]);
        }
    }

    public function subirLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:5120|mimes:jpeg,png,jpg,webp|dimensions:min_width=100,min_height=50,max_width=1000,max_height=500',
        ], [
            'logo.image' => 'El archivo debe ser una imagen válida',
            'logo.max' => 'La imagen no debe superar 5MB',
            'logo.mimes' => 'La imagen debe ser JPG, PNG, JPEG o WEBP',
            'logo.dimensions' => 'La imagen debe tener entre 100x50px y 1000x500px',
        ]);

        try {
            $company = auth()->user()->company;
            
            if (!$request->hasFile('logo') || !$request->file('logo')->isValid()) {
                return back()->withErrors(['logo' => 'Archivo inválido o corrupto']);
            }

            $this->brandingService->subirLogo($company, $request->file('logo'));
            
            return back()->with('success', 'Logo actualizado correctamente');
        } catch (\Exception $e) {
            Log::error('Error subiendo logo: ' . $e->getMessage());
            return back()->withErrors(['logo' => 'Error al subir logo: ' . $e->getMessage()]);
        }
    }
}