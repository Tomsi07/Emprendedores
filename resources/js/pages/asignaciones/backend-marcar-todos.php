<?php

// ─────────────────────────────────────────────────────────────
// 1) routes/web.php
//    Dentro del mismo grupo con middleware 'auth' que ya usan las
//    rutas de asignaciones, y ANTES de la ruta
//    PATCH /asignaciones/{evento}/{emprendimiento}
//    (si no, Laravel interpreta "masivo" como un id de emprendimiento).
//
//    Después de agregarla corré:  php artisan route:clear
// ─────────────────────────────────────────────────────────────

Route::patch('/asignaciones/{evento}/masivo', [AsignacionController::class, 'updateMasivo'])
    ->name('asignaciones.updateMasivo');


// ─────────────────────────────────────────────────────────────
// 2) app/Http/Controllers/AsignacionController.php
//    (usá el nombre real de tu controlador).
//    Necesita:  use Illuminate\Http\Request;
//               use Illuminate\Support\Facades\DB;
// ─────────────────────────────────────────────────────────────

