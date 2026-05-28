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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // Información básica
            $table->string('nombre', 150)->unique();
            $table->string('email', 150)->unique();
            $table->text('descripcion')->nullable();
            
            // Subdomain para acceso
            $table->string('subdomain', 100)->unique();
            
            // Plan y suscripción
            $table->enum('plan_tipo', ['basic', 'professional', 'enterprise'])->default('basic');
            $table->date('fecha_inicio_suscripcion');
            $table->date('fecha_fin_suscripcion');
            $table->boolean('activa')->default(true);
            
            // Límites
            $table->integer('limite_usuarios')->default(20);
            
            // Configuración
            $table->string('zona_horaria', 50)->default('America/Guayaquil');
            $table->string('idioma', 5)->default('es');
            $table->json('caracteristicas_habilitadas')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index('activa');
            $table->index('plan_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
