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
        Schema::create('schedules', function (Blueprint $table) {
        $table->id();
        
        // Tenant
        $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
        
        // Qué, Dónde, Quién
        $table->foreignId('service_id')->constrained()->onDelete('cascade');
        $table->foreignId('resource_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Instructor (opcional)
        
        // Cuándo (horario semanal recurrente)
        $table->tinyInteger('day_of_week'); // 0=Domingo, 1=Lunes, ..., 6=Sábado
        $table->time('start_time'); // Ej: 10:00:00
        
        // Capacidad de este turno específico
        $table->integer('capacity');
        
        // Estado
        $table->boolean('is_active')->default(true);
        
        $table->softDeletes();
        $table->timestamps();
        
        // Índices para búsquedas y validaciones
        $table->index(['tenant_id', 'day_of_week', 'start_time']);
        $table->index(['resource_id', 'day_of_week', 'start_time']);
        $table->index(['user_id', 'day_of_week', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
