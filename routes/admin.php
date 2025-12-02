<?php

use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

/**
 * Rutas del Backoffice (Admin)
 * 
 * Middleware aplicado: auth, verified, role:admin
 * Prefix: /admin
 */

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Admin
    Route::get('/dashboard', function () {
        return inertia('Admin/Dashboard');
    })->name('dashboard');
    
    // Infraestructura del Estudio
    Route::resource('resources', ResourceController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    
    // TODO Sprint 3: Agenda
    // Route::resource('schedule-blocks', ScheduleBlockController::class);
    
    // TODO Sprint 4: Alumnos
    // Route::resource('students', StudentController::class);
    
    // TODO Sprint 9: Cobranza
    // Route::resource('payments', PaymentController::class);
});