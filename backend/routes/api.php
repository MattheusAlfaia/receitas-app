<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ReceitaController;

Route::middleware('web')->prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')->name('register');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')->name('login');

    Route::middleware('auth:web')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('me');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::middleware(['web', 'auth:web'])->group(function () {
    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');

    Route::get(
        '/receitas/search',
        [ReceitaController::class, 'search']
    )->name('receitas.search');

    Route::get('/receitas/{receita}/imagem', [ReceitaController::class, 'image'])
        ->name('receitas.image');

    Route::apiResource('receitas', ReceitaController::class);
});
