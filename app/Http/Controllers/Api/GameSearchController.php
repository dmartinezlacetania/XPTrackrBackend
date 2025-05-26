<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\GameLibrary;
use Illuminate\Support\Facades\Auth;
use Laravel\Pail\ValueObjects\Origin\Console;

class GameSearchController extends Controller
{
    private const RAWG_URL = 'https://api.rawg.io/api/games';
    private const PAGE_SIZE = 10;

    // Funció per cercar jocs a l'API de RAWG
    public function search(Request $request)
    {
        $validated = $request->validate([
            'search' => 'sometimes|string|max:255',
            'page' => 'sometimes|integer|min:1'
        ]);

        $response = Http::get(self::RAWG_URL, [
            'key' => config('services.rawg.key'),
            'search' => $validated['search'] ?? '',
            'page' => $validated['page'] ?? 1,
            'page_size' => self::PAGE_SIZE
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            // Si el usuario está autenticado, añadimos información de biblioteca
            if (Auth::check()) {
                $gameIds = collect($data['results'])->pluck('id')->toArray();
                $libraryEntries = GameLibrary::where('user_id', Auth::id())
                    ->whereIn('game_id', $gameIds)
                    ->get()
                    ->keyBy('game_id');
                
                // Añadir estado de biblioteca a cada juego
                foreach ($data['results'] as &$game) {
                    $game['library_status'] = $libraryEntries->has($game['id']) 
                        ? $libraryEntries[$game['id']]->status 
                        : null;
                }
            }
            
            return response()->json($data);
        }

        return response()->json([
            'error' => 'Error al consultar la API de RAWG'
        ], $response->status());
    }

    // Funció per obtenir els detalls d'un joc específic
    public function show($id)
    {
        $response = Http::get(self::RAWG_URL . "/{$id}", [
            'key' => config('services.rawg.key')
        ]);
    
        if ($response->successful()) {
            $gameData = $response->json();
            
            // Si el usuario está autenticado, verificamos si el juego está en su biblioteca
            if (Auth::check()) {
                $libraryEntry = GameLibrary::where('user_id', Auth::id())
                    ->where('game_id', $id)
                    ->first();
                    
                if ($libraryEntry) {
                    $gameData['library_status'] = $libraryEntry->status;
                    $gameData['library_notes'] = $libraryEntry->notes;
                    $gameData['library_rating'] = $libraryEntry->rating;
                } else {
                    $gameData['library_status'] = null;
                    $gameData['library_notes'] = null;
                    $gameData['library_rating'] = null;
                }
            }
            
            return response()->json($gameData);
        }
    
        return response()->json([
            'error' => 'Juego no encontrado'
        ], 404);
    }

    // Funció per obtenir els propers llançaments de jocs
    public function next_games()
    {
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $nextWeek = date('Y-m-d', strtotime('+30 days'));

        $queryParams = [
            'dates' => "{$tomorrow},{$nextWeek}",
            'ordering' => 'released',
            'key' => config('services.rawg.key'),
            'page_size' => self::PAGE_SIZE
        ];

        $response = Http::get(self::RAWG_URL, $queryParams);

        if ($response->successful()) {
            $data = $response->json();
            return response()->json($data);
        }

        return response()->json([
            'error' => 'Error al consultar la API de RAWG para los juegos de la próxima semana',
            'details' => $response->json()
        ], $response->status());
    }
}
