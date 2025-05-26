<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\GameSearchController;
use App\Http\Controllers\Api\LibraryController;


// Rutes d'autenticació
Route::post('register', [AuthController::class, 'register']); // Registre d'usuaris nous
Route::get('/sanctum/csrf-cookie', [AuthController::class, 'csrfCookie']); // Obtenció de cookie CSRF
Route::post('login', [AuthController::class, 'login']); // Inici de sessió

// Grup de rutes protegides per autenticació
Route::middleware('auth:sanctum')->group(function () {
    // Rutes de gestió d'usuaris
    Route::post('logout', [AuthController::class, 'logout']); // Tancament de sessió
    Route::get('user', function (Request $request) { // Obtenció de l'usuari actual
        return $request->user();
    });
    Route::get('/user/{id}', function ($id) { // Obtenció d'un usuari específic
        return \App\Models\User::findOrFail($id);
    });
    Route::post('update-profile', [AuthController::class, 'updateProfile']); // Actualització del perfil
    Route::post('update-avatar', [AuthController::class, 'updateAvatar']); // Actualització de l'avatar
});

// Rutes de cerca de jocs (públiques)
Route::get('/games', [GameSearchController::class, 'search']); // Cerca de jocs
Route::get('/games/next-games', [GameSearchController::class, 'next_games']); // Propers llançaments
Route::get('/games/{id}', [GameSearchController::class, 'show']); // Detalls d'un joc

// Grup de rutes protegides per autenticació
Route::middleware('auth:sanctum')->group(function () {
    // Rutes de la biblioteca de jocs
    Route::get('/library/{userId?}', [LibraryController::class, 'index']); // Llistat de la biblioteca
    Route::get('/library/{userId?}/{gameId}', [LibraryController::class, 'show']); // Detalls d'un joc a la biblioteca
    Route::post('/library', [LibraryController::class, 'store']); // Afegir joc a la biblioteca
    Route::put('/library/{gameId}', [LibraryController::class, 'update']); // Actualitzar estat d'un joc
    Route::delete('/library/{gameId}', [LibraryController::class, 'destroy']); // Eliminar joc de la biblioteca

    // Rutes d'amics
    Route::get('/users', [FriendController::class, 'users']); // Cerca d'usuaris
    Route::get('/friends', [FriendController::class, 'index']); // Llistat d'amics
    Route::get('/friends/requests', [FriendController::class, 'receivedRequests']); // Sol·licituds pendents
    Route::post('/friends', [FriendController::class, 'store']); // Enviar sol·licitud d'amistat
    Route::post('/friends/{id}/accept', [FriendController::class, 'accept']); // Acceptar sol·licitud
    Route::delete('/friends/{id}', [FriendController::class, 'destroy']); // Eliminar amistat
});
