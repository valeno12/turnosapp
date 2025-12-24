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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Relaciones
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('resource_id')->nullable()->constrained()->onDelete('set null');

            // Fecha específica de la clase
            $table->date('date'); // 2024-12-16
            
            // Estado
            $table->enum('status', ['confirmed', 'cancelled', 'attended', 'no_show'])->default('confirmed');
            
            // Notas
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['tenant_id', 'date']);
            $table->index(['schedule_id', 'date']);
            $table->index(['student_id', 'date']);
            
            // Constraint: un alumno no puede tener 2 reservas en el mismo schedule el mismo día
            $table->unique(['student_id', 'schedule_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
