<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Obtener el dominio/subdomain del request
        $host = $request->getHost(); // "maria.turnosapp.com"
        
        // 2. Extraer solo el subdomain (la primera parte)
        $subdomain = explode('.', $host)[0]; // "maria"
        
        // 3. Buscar el tenant en la DB
        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();
        
        // 4. Si no existe, tirar 404
        if (!$tenant) {
            abort(404, 'Estudio no encontrado');
        }
        
        // 5. Guardar el tenant en el "contenedor" de Laravel
        // Esto hace que esté disponible en TODA la app
        app()->instance('tenant', $tenant);
        
        // 6. Compartir con Vue (Inertia)
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share('tenant', [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'subdomain' => $tenant->subdomain,
                'config' => [
                    'logo_url' => $tenant->logo_url,
                    'primary_color' => $tenant->primary_color,
                ]
            ]);
        }
        
        // 7. Dejar pasar el request
        return $next($request);
    }
}
