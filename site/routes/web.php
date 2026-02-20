<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SensController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/comprendre/{slug}', [SensController::class, 'show'])->name('sens.show');

Route::get('/conseils', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/conseils/{slug}', [ArticleController::class, 'show'])->name('articles.show');

Route::get('/faq', [FaqController::class, 'index'])->name('faq');

Route::get('/salle-snoezelen-{city}', [PageController::class, 'location'])->name('page.location');

Route::get('/api/availability/next', [AvailabilityController::class, 'next']);

// Catch-all for dynamic pages (must be last)
Route::get('/{slug}', [PageController::class, 'show'])->name('page.show')
    ->where('slug', '^(?!admin|api|livewire).*$');
