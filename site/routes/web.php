<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\PremiereSeanceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SensController;
use App\Http\Controllers\Admin\MediaApiController;
use Illuminate\Support\Facades\Route;

// Admin media API (for MediaPicker component)
Route::middleware(['web', 'auth'])->prefix('admin/media-api')->group(function () {
    Route::get('/', [MediaApiController::class, 'index'])->name('admin.media-api.index');
    Route::post('/upload', [MediaApiController::class, 'upload'])->name('admin.media-api.upload');
    Route::put('/{media}', [MediaApiController::class, 'update'])->name('admin.media-api.update');
    Route::delete('/{media}', [MediaApiController::class, 'destroy'])->name('admin.media-api.destroy');
    Route::put('/folders/rename', [MediaApiController::class, 'renameFolder'])->name('admin.media-api.rename-folder');
});

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/comprendre/{slug}', [SensController::class, 'show'])->name('sens.show');

Route::get('/conseils', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/conseils/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');

Route::get('/salle-snoezelen-{city}', [PageController::class, 'location'])->name('page.location');

Route::get('/api/availability/next', [AvailabilityController::class, 'next']);

Route::get('/voyage-sensoriel', [ExperienceController::class, 'index'])->name('experience');
Route::get('/premiere-seance', [PremiereSeanceController::class, 'index'])->name('premiere-seance');

Route::get('/cadeau-naissance-sensoriel', [LandingPageController::class, 'cadeauNaissance'])->name('cadeau-naissance');
Route::get('/cadeau-femme-enceinte-sensoriel', [LandingPageController::class, 'cadeauFemmeEnceinte'])->name('cadeau-femme-enceinte');
Route::get('/seance-snoezelen-autisme', [LandingPageController::class, 'snoezlenAutisme'])->name('snoezelen-autisme');
Route::get('/seance-snoezelen-alzheimer', [LandingPageController::class, 'snoezlenAlzheimer'])->name('snoezelen-alzheimer');

// Catch-all for dynamic pages (must be last)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show')
    ->where('slug', '^(?!admin|api|livewire).*$');
