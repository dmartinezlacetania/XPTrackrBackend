<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    // Funció per obtenir la llista d'amics confirmats
    public function index()
    {
        $userId = Auth::id();
        $friends = Friend::where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('friend_id', $userId);
            })
            ->where('status', 'accepted')
            ->get();

        return response()->json($friends);
    }

    // Funció per enviar una sol·licitud d'amistat
    public function store(Request $request)
    {
        $validated = $request->validate([
            'friend_id' => 'required|exists:users,id'
        ]);

        if ($validated['friend_id'] == Auth::id()) {
            return response()->json(['error' => 'No puedes enviarte una solicitud a ti mismo'], 400);
        }

        $friendship = Friend::firstOrCreate(
            [
                'user_id' => Auth::id(),
                'friend_id' => $validated['friend_id']
            ],
            [
                'status' => 'pending'
            ]
        );

        return response()->json($friendship);
    }

    // Funció per acceptar una sol·licitud d'amistat
    public function accept($id)
    {
        // Buscar la solicitud por su ID principal
        $friendship = Friend::where('id', $id)
            ->where('friend_id', Auth::id()) // Verificar que el receptor sea el usuario autenticado
            ->where('status', 'pending')
            ->firstOrFail();
    
        $friendship->status = 'accepted';
        $friendship->save();
    
        return response()->json($friendship);
    }

    // Funció per eliminar una amistat o rebutjar una sol·licitud
    public function destroy($id)
    {
        $friendship = Friend::where('id', $id)
            ->where(function($q) {
                $q->where('user_id', Auth::id())
                  ->orWhere('friend_id', Auth::id());
            })
            ->firstOrFail();

        $friendship->delete();

        return response()->json(['message' => 'Amistad eliminada o solicitud rechazada']);
    }

    // Funció per cercar usuaris
    public function users(Request $request)
    {
        $search = $request->input('search');
        $query = User::query()
            ->where('id', '!=', Auth::id());

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->limit(20)->get();

        return response()->json($users);
    }

    // Funció per obtenir les sol·licituds d'amistat pendents
    public function receivedRequests()
    {
        $userId = Auth::id();
        $requests = Friend::where('friend_id', $userId)
            ->where('status', 'pending')
            ->get();

        return response()->json($requests);
    }
}
