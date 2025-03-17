<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Labor Rates Management
    Route::middleware([\App\Http\Middleware\EnsureUserHasTeam::class])->prefix('labor-rates')->name('labor-rates.')->group(function () {
        Route::get('/', App\Livewire\LaborRates\Index::class)->name('index');
        Route::get('/create', App\Livewire\LaborRates\Create::class)->name('create');
        Route::get('/{laborRate}/edit', App\Livewire\LaborRates\Edit::class)->name('edit');
    });
});
