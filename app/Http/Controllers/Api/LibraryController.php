<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LibraryController extends Controller
{
    // Funció per obtenir tots els jocs de la biblioteca d'un usuari
    public function index($userId = null)
    {
        $targetUserId = $userId ?? Auth::id();
        $games = GameLibrary::where('user_id', $targetUserId)->get();
        return response()->json($games);
    }

    // Funció per obtenir els detalls d'un joc específic de la biblioteca
    public function show($userId = null, $gameId)
    {
        $targetUserId = $userId ?? Auth::id();
        $game = GameLibrary::where('user_id', $targetUserId)
                       ->where('rawg_id', $gameId)
                       ->firstOrFail();
        return response()->json($game);
    }

    // Funció per afegir o actualitzar un joc a la biblioteca
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rawg_id' => 'required|integer',
            'status' => 'required|in:playing,plan_to_play,completed,dropped,on_hold',
            'notes' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:10'
        ]);

        $library = GameLibrary::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'rawg_id' => $validated['rawg_id']
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'rating' => $validated['rating'] ?? null
            ]
        );

        return response()->json($library);
    }

    // public function show($rawgId)
    // {
    //     $entry = GameLibrary::where('user_id', Auth::id())
    //         ->where('rawg_id', $rawgId)
    //         ->first();

    //     return response()->json($entry);
    // }

    public function update(Request $request, $rawgId)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:playing,plan_to_play,completed,dropped,on_hold',
            'notes' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:10'
        ]);

        $entry = GameLibrary::where('user_id', Auth::id())
            ->where('rawg_id', $rawgId)
            ->first();

        if (!$entry) {
            return response()->json(['message' => 'Juego no encontrado en la biblioteca'], 404);
        }

        $entry->update($validated);

        return response()->json($entry);
    }

    // Funció per eliminar un joc de la biblioteca
    public function destroy($rawgId)
    {
        GameLibrary::where('user_id', Auth::id())
            ->where('rawg_id', $rawgId)
            ->delete();

        return response()->json(['message' => 'Juego eliminado de la biblioteca']);
    }
}