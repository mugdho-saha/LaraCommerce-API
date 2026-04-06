<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
{
    // 1. Manually check credentials instead of using $request->authenticate() 
    // if that method still triggers session errors.
    $credentials = $request->only('email', 'password');

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Invalid login details'
        ], 401);
    }

    $user = User::where('email', $request->email)->firstOrFail();

    // 2. Create the Sanctum Token
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Login successful',
        'access_token' => $token,
        'token_type' => 'Bearer',
    ]);
}
    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
{
    // 1. Revoke the token that was used for this request
    $request->user()->currentAccessToken()->delete();

    // 2. Return the JSON response
    return response()->json([
        'status' => 'success',
        'message' => 'Successfully logged out. Token has been revoked.'
    ], 200);
}
}
