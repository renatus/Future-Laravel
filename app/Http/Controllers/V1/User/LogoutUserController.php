<?php

namespace App\Http\Controllers\V1\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogoutUserController extends Controller
{
    /**
     * User logout
     *
     * Only current token will be deleted, other user-associated tokens will persist.
     * It is expected behaviour, useful if multiple clients are being used by a single person.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     *
     * @OA\Get(
     *      path="/logout",
     *      operationId="logoutUser",
     *      tags={"User"},
     *      summary="Logout user",
     *      description="Logs user out.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation.",
     *          @OA\JsonContent(ref="#/components/schemas/LogoutUser200")
     *      ),
     *      @OA\Response(response=401, ref="#/components/responses/401"),
     *      @OA\Response(response=405, ref="#/components/responses/405"),
     *      @OA\Response(response=429, ref="#/components/responses/429"),
     * ),
     * @OA\Schema(
     *     schema="LogoutUser200",
     *     description="User has logged out successfully",
     *     @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
     * ),
     */
    public function __invoke(Request $request)
    {
        // Delete user's current access token
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Successful logout.',
        ], 200);
    }
}
