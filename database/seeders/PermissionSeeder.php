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
                'name' => 'access_resources',
                'description' => 'Acceso a recursos (ver y gestionar)',
                'group' => 'resources',
            ],

            // SERVICIOS
            [
                'name' => 'access_services',
                'description' => 'Acceso a servicios (ver y gestionar)',
                'group' => 'services',
            ],

            // PLANES
            [
                'name' => 'access_plans',
                'description' => 'Acceso a planes (ver y gestionar)',
                'group' => 'plans',
            ],

            // USUARIOS
            [
                'name' => 'access_users',
                'description' => 'Acceso a usuarios (ver, crear, editar)',
                'group' => 'users',
            ],
            [
                'name' => 'manage_permissions',
                'description' => 'Gestionar permisos de usuarios',
                'group' => 'users',
            ],

            // HORARIOS
            [
                'name' => 'access_schedule',
                'description' => 'Acceso a horarios (ver y gestionar)',
                'group' => 'schedule',
            ],

            // RESERVAS
            [
                'name' => 'access_bookings',
                'description' => 'Acceso a reservas (ver y gestionar)',
                'group' => 'bookings',
            ],

            // REPORTES
            [
                'name' => 'access_reports',
                'description' => 'Acceso a reportes básicos',
                'group' => 'reports',
            ],
            [
                'name' => 'access_financial',
                'description' => 'Acceso a reportes financieros',
                'group' => 'reports',
            ],

            // CONFIGURACIÓN
            [
                'name' => 'access_settings',
                'description' => 'Acceso a configuración del estudio',
                'group' => 'settings',
            ],
            // ALUMNOS
            [
                'name' => 'access_students',
                'description' => 'Acceso a alumnos (ver, crear, editar)',
                'group' => 'students',
            ],
            [
                'name' => 'access_schedules',
                'description' => 'Acceso a horarios (ver y gestionar)',
                'group' => 'schedules',
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
