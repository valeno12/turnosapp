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
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            
            // Tenant
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Schedule que se cancela
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            
            // Fecha específica cancelada
            $table->date('date'); // 2024-12-25 (Navidad)
            
            // Motivo de la cancelación
            $table->string('reason')->nullable(); // "Feriado", "Instructor ausente", etc.
            
            $table->timestamps();
            
            // Índices
            $table->index(['tenant_id', 'date']);
            $table->index(['schedule_id', 'date']);
            
            // Constraint: un schedule no puede tener 2 excepciones el mismo día
            $table->unique(['schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
