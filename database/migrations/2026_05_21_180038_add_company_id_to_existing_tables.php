<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualizar usuarios a company_id = 1
        DB::statement('UPDATE usuarios SET company_id = 1 WHERE company_id IS NULL OR company_id = 0');
        
        // Agregar company_id a logs si no existe
        if (!Schema::hasColumn('logs', 'company_id')) {
            Schema::table('logs', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->default(1)->after('id');
                $table->index('company_id');
            });
        }

        // Agregar company_id a permisos si no existe
        if (!Schema::hasColumn('permisos', 'company_id')) {
            Schema::table('permisos', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->default(1)->after('id');
                $table->index('company_id');
            });
        }

        // Agregar company_id a comandos si existe la tabla
        if (Schema::hasTable('comandos') && !Schema::hasColumn('comandos', 'company_id')) {
            Schema::table('comandos', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->default(1)->after('id');
                $table->index('company_id');
            });
        }
        
        // Actualizar los nuevos campos
        if (Schema::hasColumn('logs', 'company_id')) {
            DB::statement('UPDATE logs SET company_id = 1 WHERE company_id = 0');
        }
        if (Schema::hasColumn('permisos', 'company_id')) {
            DB::statement('UPDATE permisos SET company_id = 1 WHERE company_id = 0');
        }
        if (Schema::hasTable('comandos') && Schema::hasColumn('comandos', 'company_id')) {
            DB::statement('UPDATE comandos SET company_id = 1 WHERE company_id = 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        // Revertir logs
        Schema::table('logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        // Revertir permisos
        Schema::table('permisos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
        });

        // Revertir comandos
        if (Schema::hasTable('comandos')) {
            Schema::table('comandos', function (Blueprint $table) {
                $table->dropConstrainedForeignId('company_id');
            });
        }
    }
};
