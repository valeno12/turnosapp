<?php
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // RESOURCES
    Route::middleware('permission:manage_resources')->group(function () {
        Route::get('/resources/create', [ResourceController::class, 'create'])->name('resources.create');
        Route::post('/resources', [ResourceController::class, 'store'])->name('resources.store');
        Route::get('/resources/{resource}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
        Route::put('/resources/{resource}', [ResourceController::class, 'update'])->name('resources.update');
        Route::delete('/resources/{resource}', [ResourceController::class, 'destroy'])->name('resources.destroy');
    });
    
    Route::middleware('permission:view_resources,manage_resources')->group(function () {
        Route::get('/resources', [ResourceController::class, 'index'])->name('resources.index');
        Route::get('/resources/{resource}', [ResourceController::class, 'show'])->name('resources.show');
    });
    
    // SERVICES
    Route::middleware('permission:manage_services')->group(function () {
        Route::get('/services/create', [ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    });
    
    Route::middleware('permission:view_services,manage_services')->group(function () {
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    });
    
    // SUBSCRIPTION PLANS
    Route::middleware('permission:manage_plans')->group(function () {
        Route::get('/plans/create', [SubscriptionPlanController::class, 'create'])->name('plans.create');
        Route::post('/plans', [SubscriptionPlanController::class, 'store'])->name('plans.store');
        Route::get('/plans/{plan}/edit', [SubscriptionPlanController::class, 'edit'])->name('plans.edit');
        Route::put('/plans/{plan}', [SubscriptionPlanController::class, 'update'])->name('plans.update');
        Route::delete('/plans/{plan}', [SubscriptionPlanController::class, 'destroy'])->name('plans.destroy');
    });
    
    Route::middleware('permission:view_plans,manage_plans')->group(function () {
        Route::get('/plans', [SubscriptionPlanController::class, 'index'])->name('plans.index');
    });
    
        Route::middleware('permission:manage_users')->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    Route::middleware('permission:view_users,manage_users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
    });

});