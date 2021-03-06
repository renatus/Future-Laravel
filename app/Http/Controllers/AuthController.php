<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * User-related functionality
 */
class AuthController extends Controller
{
    /**
     * User registration
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Validate client input
        // If client sent wrong data, adequate response is being generated by Laravel
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Create new user
        $user = User::create([
            // Ordered UUIDs offer some benefits for search speed over unordered
            'id' => Str::orderedUuid()->toString(),
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            // Passwords hashed that way are salty
            'password' => Hash::make($validatedData['password']),
        ]);

        // Generate user access token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'id' => $user->id,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Successful registration.'
        ], 201);
    }

    /**
     * User login
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // Validate client input
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // If email-password pair is missing among User models
        if (!Auth::attempt($validatedData)) {
            return response()->json([
                // We do not want to specify, if e-mail exists in our DB for security reasons
                'message' => 'Wrong user credentials.'
            ], 401);
        }

        // Get matching user
        $user = User::where('email', $validatedData['email'])->firstOrFail();

        // Generate user access token
        $token = $user->createToken('auth_token')->plainTextToken;

        // If client sent wrong data, adequate response is being generated by Laravel
        return response()->json([
            'id' => $user->id,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Successfull login.'
        ], 200);
    }

    /**
     * User logout
     *
     * Only current token will be deleted, other user-associated tokens will persist.
     * It is expected behaviour, useful if multiple clients are being used by a single person.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // Delete user's current access token
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Successful logout.',
        ], 200);
    }

    public function me(Request $request)
    {
        return $request->user();
    }
}
