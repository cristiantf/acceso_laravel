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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->string('usuario_id', 20)->nullable();
            $table->string('tipo_evento', 50)->nullable();
            $table->string('origen', 50)->nullable();
            $table->float('latitud', 10, 6)->nullable();
            $table->float('longitud', 10, 6)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('foto_path', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
