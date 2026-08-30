<?php

use App\Http\Controllers\CvController;
use App\Http\Controllers\CvPhotoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [CvController::class, 'landing'])->name('landing');

/*
 * Liste purement cliente : les CV du visiteur vivent dans son localStorage,
 * le serveur n'a aucun moyen de savoir lesquels lui appartiennent.
 */
Route::get('/mes-cv', fn () => Inertia::render('MyCvs'))->name('cv.mine');

// Le service est ouvert sans compte : on limite la creation et l'upload.
Route::post('/cv', [CvController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('cv.store');

Route::get('/cv/{cv}/edit', [CvController::class, 'edit'])->name('cv.edit');
Route::get('/cv/{cv}', [CvController::class, 'show'])->name('cv.show');

// Ecritures : reservees au porteur du jeton d'edition.
Route::middleware('cv.token')->group(function () {
    Route::patch('/cv/{cv}', [CvController::class, 'update'])->name('cv.update');
    Route::delete('/cv/{cv}', [CvController::class, 'destroy'])->name('cv.destroy');

    Route::post('/cv/{cv}/photo', [CvPhotoController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('cv.photo.store');

    Route::delete('/cv/{cv}/photo', [CvPhotoController::class, 'destroy'])->name('cv.photo.destroy');
});
