<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BrandingService;
use Illuminate\Http\Request;

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
            'nombre_sistema' => 'nullable|string|max:100',
            'subtitulo' => 'nullable|string|max:200',
            'nombre_empresa' => 'nullable|string|max:150',
            'pie_pagina' => 'nullable|string|max:300',
            'colores.primario' => 'nullable|regex:/#[a-f0-9]{6}/',
            'colores.secundario' => 'nullable|regex:/#[a-f0-9]{6}/',
            'colores.acento' => 'nullable|regex:/#[a-f0-9]{6}/',
            'tema' => 'in:light,dark,custom',
            'mostrar_marca_agua' => 'boolean',
        ]);

        $company = auth()->user()->company;
        
        // Extraer solo los campos de colores enviados
        $colores = collect($validated)
            ->filter(fn($v, $k) => str_starts_with($k, 'colores.'))
            ->mapWithKeys(fn($v, $k) => [str_replace('colores.', '', $k) => $v])
            ->toArray();

        $textos = [
            'nombre_sistema' => $validated['nombre_sistema'] ?? null,
            'subtitulo' => $validated['subtitulo'] ?? null,
            'nombre_empresa' => $validated['nombre_empresa'] ?? null,
            'pie_pagina' => $validated['pie_pagina'] ?? null,
        ];

        $this->brandingService->guardarBranding($company, [
            'colores' => $colores,
            'textos' => array_filter($textos),
            'tema' => $validated['tema'] ?? 'light',
            'mostrar_marca_agua' => $validated['mostrar_marca_agua'] ?? false,
        ]);

        return back()->with('success', 'Branding actualizado correctamente');
    }

    public function subirLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|max:5120|dimensions:min_width=100',
        ]);

        $company = auth()->user()->company;
        $this->brandingService->subirLogo($company, $request->file('logo'));

        return back()->with('success', 'Logo actualizado correctamente');
    }
}