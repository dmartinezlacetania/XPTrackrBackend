<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\GameSearchController;
use App\Http\Controllers\Api\LibraryController;


Route::post('register', [AuthController::class, 'register']);
Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', function (Request $request) {
        return $request->user();
    });
    Route::get('/user/{id}', function ($id) {
        return \App\Models\User::findOrFail($id);
    });
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
    Route::post('update-avatar', [AuthController::class, 'updateAvatar']);
});

// Rutas para búsqueda de juegos (no requieren autenticación)
Route::get('/games', [GameSearchController::class, 'search']);
Route::get('/games/{id}', [GameSearchController::class, 'show']);
Route::get('/games/next-week', [GameSearchController::class, 'next_week_games']);

Route::middleware('auth:sanctum')->group(function () {
    // Rutas para la biblioteca de juegos (requieren autenticación)
    Route::get('/library', [LibraryController::class, 'index']);
    Route::post('/library', [LibraryController::class, 'store']);
    Route::get('/library/{gameId}', [LibraryController::class, 'show']);
    Route::put('/library/{gameId}', [LibraryController::class, 'update']);
    Route::delete('/library/{gameId}', [LibraryController::class, 'destroy']);

    // Friends
    Route::get('/users', [FriendController::class, 'users']);
    Route::get('/friends', [FriendController::class, 'index']);
    Route::get('/friends/requests', [FriendController::class, 'receivedRequests']);
    Route::post('/friends', [FriendController::class, 'store']);
    Route::post('/friends/{id}/accept', [FriendController::class, 'accept']);
    Route::delete('/friends/{id}', [FriendController::class, 'destroy']);
});
