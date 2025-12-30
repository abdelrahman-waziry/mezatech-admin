<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    /**
     * Handle admin login.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Generate token
        // Note: You may need to install Laravel Sanctum (composer require laravel/sanctum)
        // or use your preferred JWT package. For now, this is a placeholder.
        // If using Sanctum, uncomment the line below:
        // $token = $user->createToken('admin-token')->plainTextToken;
        
        // Placeholder token generation - replace with your actual token generation logic
        $token = base64_encode(json_encode([
            'name' => $user->name,
            'email' => $user->email,
            'sub' => $user->email,
            'iat' => now()->timestamp,
            'exp' => now()->addDays(1)->timestamp,
        ]));

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}

