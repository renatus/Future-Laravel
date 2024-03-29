<?php

namespace App\Http\Controllers\V1\User;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class LoginUserController extends Controller
{
    /**
     * User login
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     *
     * @OA\RequestBody(
     *     request="loginUserRequest",
     *     description="User credentials to log in to server",
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             required={"email", "password"},
     *             @OA\Property(property="email", ref="#/components/schemas/Email"),
     *             @OA\Property(property="password", ref="#/components/schemas/Password"),
     *         ),
     *     ),
     * ),
     * @OA\Post(
     *      path="/login",
     *      operationId="loginUser",
     *      tags={"User"},
     *      summary="Login user",
     *      description="Logs user in.",
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      requestBody={"$ref": "#/components/requestBodies/loginUserRequest"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation.",
     *          @OA\JsonContent(ref="#/components/schemas/LoginUser200")
     *      ),
     *      @OA\Response(response=401, ref="#/components/responses/401"),
     *      @OA\Response(response=405, ref="#/components/responses/405"),
     *      @OA\Response(response=422, ref="#/components/responses/422"),
     *      @OA\Response(response=429, ref="#/components/responses/429"),
     *      @OA\Response(response=500, ref="#/components/responses/500"),
     * ),
     * @OA\Schema(
     *     schema="LoginUser200",
     *     description="User has logged in successfully",
     *     @OA\Property(property="id", ref="#/components/schemas/EntryUuid"),
     *     @OA\Property(property="access_token", ref="#/components/schemas/AccessToken"),
     *     @OA\Property(property="token_type", ref="#/components/schemas/AccessTokenType"),
     *     @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
     * ),
     */
    public function __invoke(Request $request)
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
}
