<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GameSearchController;
use App\Http\Controllers\Api\LibraryController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// require __DIR__.'/auth.php';

Route::post('register', [AuthController::class, 'register']);
Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Añade estas rutas a tu archivo api.php

// Rutas para búsqueda de juegos (no requieren autenticación)
Route::get('/games', [App\Http\Controllers\Api\GameSearchController::class, 'search']);
Route::get('/games/{id}', [App\Http\Controllers\Api\GameSearchController::class, 'show']);

// Rutas para la biblioteca de juegos (requieren autenticación)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/library', [LibraryController::class, 'index']);
    Route::post('/library', [LibraryController::class, 'store']);
    Route::get('/library/{gameId}', [LibraryController::class, 'show']);
    Route::put('/library/{gameId}', [LibraryController::class, 'update']);
    Route::delete('/library/{gameId}', [LibraryController::class, 'destroy']);
});
