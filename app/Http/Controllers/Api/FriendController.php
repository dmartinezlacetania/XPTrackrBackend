<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendController extends Controller
{
    // Listar amigos confirmados
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

    // Enviar solicitud de amistad
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

    // Aceptar solicitud de amistad
    public function accept($id)
    {
        $friendship = Friend::where('user_id', $id)
            ->where('friend_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->status = 'accepted';
        $friendship->save();

        return response()->json($friendship);
    }

    // Rechazar o eliminar amistad
    public function destroy($id)
    {
        $userId = Auth::id();

        $friendship = Friend::where(function($q) use ($userId, $id) {
                $q->where('user_id', $userId)->where('friend_id', $id);
            })
            ->orWhere(function($q) use ($userId, $id) {
                $q->where('user_id', $id)->where('friend_id', $userId);
            })
            ->firstOrFail();

        $friendship->delete();

        return response()->json(['message' => 'Amistad eliminada o solicitud rechazada']);
    }

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

    // Solicitudes de amistad recibidas (pendientes)
    public function receivedRequests()
    {
        $userId = Auth::id();
        $requests = Friend::where('friend_id', $userId)
            ->where('status', 'pending')
            ->get();

        return response()->json($requests);
    }
}
