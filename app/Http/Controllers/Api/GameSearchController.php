<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameSearchController extends Controller
{
    private const RAWG_URL = 'https://api.rawg.io/api/games';
    private const PAGE_SIZE = 10;

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
            return response()->json($response->json());
        }

        return response()->json([
            'error' => 'Error al consultar la API de RAWG'
        ], $response->status());
    }
}
