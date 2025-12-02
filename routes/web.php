<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

/**
 * Rutas Públicas
 */
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

/**
 * Dashboard General (redirige según rol)
 */
Route::get('/dashboard', function () {
    $user = Auth::user();
    
    // Redirigir según rol
    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    
    if ($user->role === 'instructor') {
        return redirect()->route('instructor.dashboard');
    }
    
    // Student por defecto
    return redirect()->route('student.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/**
 * Módulos
 */
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';

// TODO Sprint 6: Descomentar cuando estén listos
// require __DIR__.'/student.php';
// require __DIR__.'/instructor.php';