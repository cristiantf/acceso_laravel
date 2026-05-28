<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyBranding;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear empresa predeterminada
        $company = Company::create([
            'id' => 1, // Importante: usar ID 1 para compatibilidad
            'nombre' => 'ISTAE Control Biométrico',
            'email' => 'admin@istae.local',
            'descripcion' => 'Empresa predeterminada del sistema',
            'subdomain' => 'default',
            'plan_tipo' => 'professional',
            'fecha_inicio_suscripcion' => now(),
            'fecha_fin_suscripcion' => now()->addYear(),
            'activa' => true,
            'limite_usuarios' => 100,
            'zona_horaria' => 'America/Guayaquil',
            'idioma' => 'es',
            'caracteristicas_habilitadas' => [
                'reportes_avanzados',
                'integraciones',
                'api_access',
                'two_factor_auth',
                'branding_personalizado',
            ],
        ]);

        // Crear branding predeterminado
        CompanyBranding::create([
            'company_id' => $company->id,
            'tema' => 'light',
        ]);

        $this->command->info('✅ Empresa predeterminada ISTAE creada exitosamente');
    }
}

