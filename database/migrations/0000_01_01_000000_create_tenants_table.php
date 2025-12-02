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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            // Identificación
            $table->string('name');                    // "Estudio María Pilates"
            $table->string('subdomain')->unique();     // "maria"
            $table->string('domain')->nullable()->unique(); // "estudiomaria.com" (opcional)
            
            // Branding (White Label)
            $table->string('logo_url')->nullable();    // URL del logo en storage
            $table->string('primary_color')->default('#6366f1'); // Color de marca
            
            $table->integer('cancellation_hours')->default(6); // Tiempo límite para cancelar
            $table->enum('schedule_change_policy', ['end_of_month', 'anytime'])->default('end_of_month');
            $table->integer('schedule_change_cutoff_days')->default(7); // Días antes de fin de mes
            
            // Reglas de Precio (JSON porque es una lista compleja)
            $table->json('pricing_rules')->nullable();
            
            // Estado
            $table->boolean('is_active')->default(true);
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
