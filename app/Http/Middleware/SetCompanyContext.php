<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;

class SetCompanyContext
{
    public function handle(Request $request, Closure $next)
    {
        // Opción 1: Obtener company del usuario autenticado
        if (auth()->check()) {
            $company = auth()->user()->company;
        }
        // Opción 2: Obtener company del subdomain
        else {
            $subdomain = $this->getSubdomain($request);
            $company = Company::where('subdomain', $subdomain)->first();
        }

        if (!$company) {
            // Si estamos en local y no hay empresa, mostramos un mensaje claro en lugar de un 404 genérico
            if (in_array($request->getHost(), ['localhost', '127.0.0.1'])) {
                die('ERROR MULTI-TENANT: No se encontró la empresa por defecto. Por favor ejecuta en tu terminal: php artisan db:seed --class=CompanySeeder');
            } else {
                abort(404, 'Company not found');
            }
        }

        // Guardar en contexto global
        app()->instance('company', $company);
        
        // Compartir variables con todas las vistas Blade
        View::share('company', $company);
        View::share('branding', $company->branding);

        return $next($request);
    }

    private function getSubdomain(Request $request): string
    {
        $host = $request->getHost();
        
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return 'default';
        }
        
        $parts = explode('.', $host);
        
        return $parts[0] === 'www' ? 'default' : $parts[0];
    }
}
