<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_brandings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->onDelete('cascade');
            
            // Imágenes
            $table->string('logo_path')->nullable()->comment('Logo principal (200x60px)');
            $table->string('favicon_path')->nullable()->comment('Favicon (64x64px)');
            $table->string('login_background_path')->nullable()->comment('Fondo login');
            
            // Colores personalizados (JSON)
            $table->json('colores')->default(json_encode([
                'primario' => '#0d6efd',
                'secundario' => '#6c757d',
                'acento' => '#198754',
                'fondo_login' => '#f0f2f5',
                'texto_principal' => '#212529',
                'barra_lateral' => '#1a1d20',
                'barra_navegacion' => '#212529',
                'boton_primario' => '#0d6efd',
                'boton_hover' => '#0b5ed7',
            ]));
            
            // Textos personalizados (JSON)
            $table->json('textos')->default(json_encode([
                'nombre_sistema' => 'Sistema ISTAE',
                'subtitulo' => 'Control Biométrico de Acceso',
                'nombre_empresa' => 'Mi Institución',
                'pie_pagina' => '© 2026 Todos los derechos reservados',
                'mensaje_bienvenida' => 'Bienvenido al Sistema de Control Biométrico',
                'slogan' => 'Seguridad y Control a tu alcance',
            ]));
            
            // Tema
            $table->enum('tema', ['light', 'dark', 'custom'])->default('light');
            $table->boolean('mostrar_marca_agua')->default(false);
            $table->boolean('mostrar_logo_navbar')->default(true);
            
            // Fuente personalizada
            $table->string('fuente_personalizada')->nullable()->comment('URL de Google Fonts');
            
            // Otros
            $table->boolean('mostrar_footer')->default(true);
            $table->string('url_soporte')->nullable();
            $table->string('url_terminos')->nullable();
            $table->string('url_privacidad')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_brandings');
    }
};
