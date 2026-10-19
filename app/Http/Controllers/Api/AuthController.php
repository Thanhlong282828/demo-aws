<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string|max:255']);
        $user = User::where('email', $data['email'])->first();
        abort_unless($user && Hash::check($data['password'], $user->password), 401, 'Invalid credentials.');

        return response()->json(['data' => ['token' => $user->createToken('postman', ['*'], now()->addHours(8))->plainTextToken, 'token_type' => 'Bearer', 'expires_in' => 28800], 'message' => 'Authenticated.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['data' => null, 'message' => 'Token revoked.']);
    }
}
