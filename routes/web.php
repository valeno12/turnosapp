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


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Dashboard'); // Mismo componente para todos
    })->name('dashboard');
});
/**
 * Módulos
 */
require __DIR__.'/admin.php';
require __DIR__.'/settings.php';

// TODO Sprint 6: Descomentar cuando estén listos
// require __DIR__.'/student.php';
// require __DIR__.'/instructor.php';