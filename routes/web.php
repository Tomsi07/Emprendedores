<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmprendedorController;
use App\Http\Controllers\EmprendimientoController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\NecesidadController;
use Illuminate\Support\Facades\Route;

// Redirecciones iniciales
Route::get('/', function () {
    return redirect()->route('emprendimientos.index');
});

Route::get('/dashboard', function () {
    return redirect()->route('emprendimientos.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    
    // Perfil del usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulo de Emprendedores (Rutas manuales)
    Route::get('/emprendedores', [EmprendedorController::class, 'index'])->name('emprendedores.index');
    Route::post('/emprendedores', [EmprendedorController::class, 'store'])->name('emprendedores.store');
    Route::delete('/emprendedores/{id}', [EmprendedorController::class, 'destroy'])->name('emprendedores.destroy');

    // Los 3 CRUDs principales
    Route::resource('emprendimientos', EmprendimientoController::class);
    Route::resource('actividades', ActividadController::class);
    Route::resource('necesidades', NecesidadController::class);
});

require __DIR__.'/auth.php';