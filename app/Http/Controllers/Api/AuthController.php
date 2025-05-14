<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
// use Illuminate\Validation\ValidationException;
// use App\Http\Requests\Auth\LoginRequest;
// use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    //

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', //Mejorar la seguridad de la contraseña
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // $token = $user->createToken('api_token')->plainTextToken;

        Auth::login($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            // 'token' => $token,
        ], 201);
        
    }

    public function login(Request $request)
    {
        

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'auth_token' => $token,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'sometimes|filled|string|max:255',
            'email' => 'sometimes|filled|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => ['sometimes', 'required_with:new_password', 'filled', 'string'],
            'new_password' => [
                'nullable', 
                'filled', 
                'string', 
                'min:8', 
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ],
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048'
        ], [
            'new_password.filled' => 'La nueva contraseña no puede estar vacía.',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'new_password.regex' => 'La contraseña debe contener al menos una letra mayúscula, una minúscula, un número y un carácter especial.',
            'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.'
        ]);
    
        $updateData = [];
    
        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }
    
        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }
    
        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('avatars'), $imageName);
            
            // Si existe una imagen anterior, la eliminamos
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }
            
            $updateData['avatar'] = 'avatars/' . $imageName;
        }
    
        if ($request->has('current_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta',
                    'errors' => [
                        'current_password' => ['La contraseña actual proporcionada no coincide con nuestros registros.']
                    ]
                ], 422);
            }
            
            if ($request->filled('new_password')) {
                $updateData['password'] = bcrypt($request->new_password);
            } else {
                return response()->json([
                    'message' => 'La nueva contraseña no puede estar vacía',
                    'errors' => [
                        'new_password' => ['Debes proporcionar una nueva contraseña válida.']
                    ]
                ], 422);
            }
        }
    
        // Si no hay datos para actualizar después de todas las validaciones y procesamientos
        if (empty($updateData)) {
            return response()->json([
                'message' => 'No se proporcionaron datos válidos para actualizar o los datos son los mismos que los actuales.',
                'errors' => [
                    'general' => ['No hay campos para actualizar. Asegúrate de que los campos enviados no estén vacíos y sean diferentes a los actuales.']
                ]
            ], 422);
        }
    
        User::where('id', $user->id)->update($updateData);
        
        $updatedUser = User::find($user->id);
        
        if (isset($updateData['password'])) {
            
            $token = $updatedUser->createToken('api_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Perfil y contraseña actualizados exitosamente',
                'user' => $updatedUser,
                'auth_token' => $token
            ]);
        }
        
        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $updatedUser
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function csrfCookie(Request $request)
    {
        return response()->json(['message' => 'CSRF cookie set']);
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            
            // Crear directorio si no existe
            if (!file_exists(public_path('avatars'))) {
                mkdir(public_path('avatars'), 0755, true);
            }
            
            $image->move(public_path('avatars'), $imageName);

            // Eliminar imagen anterior con ruta completa
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            $user->avatar = $imageName;
            User::where('id', $user->id)->update(['avatar' => $user->avatar]);
        }

        return response()->json([
            'message' => 'Avatar actualizado exitosamente',
            'user' => $user
        ]);
    }
}
