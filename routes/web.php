<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\ViaticoController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ViaticoPlantillaController;


Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');
// ⚠️ Ruta de diagnóstico FUERA de middlewares (temporal)
Route::get('/debug/storage', function () {
    $rel = 'plantilla_viatico.docx';
    $full = Storage::disk('local')->path($rel);

    return response()->json([
        'base_path'   => base_path(),
        'storage_app' => storage_path('app'),
        'relative'    => $rel,
        'absolute'    => $full,
        'exists()'    => Storage::disk('local')->exists($rel),
        'is_file'     => @is_file($full),
        'is_readable' => @is_readable($full),
        'dir_list'    => @scandir(storage_path('app')) ?: [],
    ]);
})->name('debug.storage');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::resource('applicants', ApplicantController::class);
    Route::resource('surveys', SurveyController::class);
    Route::get('/viatico/crear', [ViaticoPlantillaController::class, 'create'])->name('viatico.crear');
    Route::post('/viatico/generar', [ViaticoPlantillaController::class, 'generate'])->name('viatico.generate');
    Route::get('/viaticos/create', [ViaticoController::class, 'create'])->name('viaticos.create');
    // POST devuelve archivo (no Inertia Response), ideal para descarga inmediata
    Route::post('/viaticos', [ViaticoController::class, 'store'])->name('viaticos.store');

});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
