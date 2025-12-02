<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Tenant 1: Estudio María
        $tenant1 = Tenant::create([
            'name' => 'Estudio María Pilates',
            'subdomain' => 'maria',
            'logo_url' => null,
            'primary_color' => '#ec4899', // Rosa
            'cancellation_hours' => 6,
            'schedule_change_policy' => 'end_of_month',
            'schedule_change_cutoff_days' => 7,
            'pricing_rules' => [
                ['day_start' => 1, 'day_end' => 10, 'multiplier' => 1.0],
                ['day_start' => 11, 'day_end' => 20, 'multiplier' => 1.05],
                ['day_start' => 21, 'day_end' => 31, 'multiplier' => 1.10],
            ],
            'is_active' => true,
        ]);

        // Crear Tenant 2: Gym Los Pinos
        $tenant2 = Tenant::create([
            'name' => 'Gym Los Pinos',
            'subdomain' => 'lospinos',
            'logo_url' => null,
            'primary_color' => '#10b981', // Verde
            'cancellation_hours' => 12,
            'schedule_change_policy' => 'anytime',
            'schedule_change_cutoff_days' => 0,
            'pricing_rules' => [
                ['day_start' => 1, 'day_end' => 15, 'multiplier' => 1.0],
                ['day_start' => 16, 'day_end' => 31, 'multiplier' => 1.15],
            ],
            'is_active' => true,
        ]);

        // Usuario Admin para Tenant 1
        // IMPORTANTE: Forzamos tenant_id porque no hay middleware en seeders
        User::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Admin María',
            'email' => 'admin@maria.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Usuario Admin para Tenant 2
        User::create([
            'tenant_id' => $tenant2->id,
            'name' => 'Admin Los Pinos',
            'email' => 'admin@lospinos.local',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✅ Tenants creados:');
        $this->command->info('   - maria.localhost (admin@maria.local / password)');
        $this->command->info('   - lospinos.localhost (admin@lospinos.local / password)');
    }
}
