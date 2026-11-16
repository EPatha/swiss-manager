<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login'])
        ->middleware('throttle:login');
    Route::get('/public/tournaments/{slug}', [\App\Http\Controllers\Api\V1\TournamentController::class, 'publicShow']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::get('/auth/me', [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
        Route::get('/tournaments', [\App\Http\Controllers\Api\V1\TournamentController::class, 'index']);
        Route::post('/tournaments', [\App\Http\Controllers\Api\V1\TournamentController::class, 'store']);
        Route::get('/tournaments/{tournament}', [\App\Http\Controllers\Api\V1\TournamentController::class, 'show']);
    });
});
