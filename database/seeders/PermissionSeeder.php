<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            {
        $permissions = [
            // RECURSOS
            [
                'name' => 'view_resources',
                'description' => 'Ver listado de recursos',
                'group' => 'resources',
            ],
            [
                'name' => 'manage_resources',
                'description' => 'Gestionar recursos (crear, editar, eliminar)',
                'group' => 'resources',
            ],

            // SERVICIOS
            [
                'name' => 'view_services',
                'description' => 'Ver listado de servicios',
                'group' => 'services',
            ],
            [
                'name' => 'manage_services',
                'description' => 'Gestionar servicios (crear, editar, eliminar)',
                'group' => 'services',
            ],

            // PLANES
            [
                'name' => 'view_plans',
                'description' => 'Ver listado de planes de suscripción',
                'group' => 'plans',
            ],
            [
                'name' => 'manage_plans',
                'description' => 'Gestionar planes (crear, editar, eliminar)',
                'group' => 'plans',
            ],

            // USUARIOS
            [
                'name' => 'view_users',
                'description' => 'Ver listado de usuarios',
                'group' => 'users',
            ],
            [
                'name' => 'manage_users',
                'description' => 'Gestionar usuarios (crear, editar, eliminar)',
                'group' => 'users',
            ],
            [
                'name' => 'manage_permissions',
                'description' => 'Gestionar permisos de otros usuarios',
                'group' => 'users',
            ],

            // HORARIOS
            [
                'name' => 'view_schedule',
                'description' => 'Ver grilla de horarios',
                'group' => 'schedule',
            ],
            [
                'name' => 'manage_schedule',
                'description' => 'Gestionar horarios (crear, editar, eliminar bloques)',
                'group' => 'schedule',
            ],

            // RESERVAS
            [
                'name' => 'view_bookings',
                'description' => 'Ver reservas de clases',
                'group' => 'bookings',
            ],
            [
                'name' => 'manage_bookings',
                'description' => 'Gestionar reservas (crear, cancelar, marcar asistencia)',
                'group' => 'bookings',
            ],

            // REPORTES
            [
                'name' => 'view_reports',
                'description' => 'Ver reportes y estadísticas',
                'group' => 'reports',
            ],
            [
                'name' => 'view_financial_reports',
                'description' => 'Ver reportes financieros',
                'group' => 'reports',
            ],

            // CONFIGURACIÓN
            [
                'name' => 'manage_tenant_settings',
                'description' => 'Gestionar configuración del estudio',
                'group' => 'settings',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }
    }
}
