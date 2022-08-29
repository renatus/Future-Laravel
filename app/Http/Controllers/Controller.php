<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Future API OpenApi Documentation",
 *      description="Future API OpenApi description",
 *      @OA\License(
 *          name="GNU General Public License v3.0",
 *          url="https://www.gnu.org/licenses/gpl-3.0.en.html"
 *      )
 * ),
 * @OA\Server(
 *     url="http://localhost/api/v1",
 *     description="Sandbox server (uses test data)"
 * ),
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     in="header",
 *     name="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * ),
 * @OA\Schema(
 *     schema="EntryUuid",
 *     type="string",
 *     format="uuid",
 *     minLength=36,
 *     maxLength=36,
 *     example="96e1cb8a-c35f-4df0-9b76-629f43ce1d45",
 *     description="Entry UUID",
 * ),
 * @OA\Schema(
 *     schema="SrvCreatedAt",
 *     type="string",
 *     format="date-time",
 *     example="2022-07-05T14:25:16.000000Z",
 *     description="When entry was created.",
 * ),
 * @OA\Schema(
 *     schema="SrvUpdatedAt",
 *     type="string",
 *     format="date-time",
 *     example="2022-07-05T14:25:16.000000Z",
 *     description="When entry was last updated at server's DB.",
 * ),
 * @OA\Schema(
 *     schema="Url",
 *     type="string",
 *     format="url",
 *     nullable=true,
 *     example="http://localhost/storage/images/2022/07/971645cc-b76c-46c2-b32f-7b10b3d442cf.jpg",
 *     description="URL.",
 * ),
 * @OA\Schema(
 *     schema="Name",
 *     type="string",
 *     maxLength=255,
 *     example="John Doe",
 *     description="Person's name.",
 * ),
 * @OA\Schema(
 *     schema="Company",
 *     type="string",
 *     maxLength=255,
 *     nullable=true,
 *     example="Microsoft",
 *     description="Company's name.",
 * ),
 * @OA\Schema(
 *     schema="Phone",
 *     type="string",
 *     minLength=10,
 *     maxLength=18,
 *     example="+79104153277",
 *     description="Phone number.",
 * ),
 * @OA\Schema(
 *     schema="Email",
 *     type="string",
 *     format="email",
 *     example="test@test.com",
 *     description="Email address. Must be real and unique.",
 * ),
 * @OA\Schema(
 *     schema="Birthday",
 *     type="string",
 *     format="date",
 *     nullable=true,
 *     example="2021-12-31",
 *     description="Birthday date.",
 * ),
 * @OA\Schema(
 *     schema="Picture",
 *     type="array",
 *     maximum=15000,
 *     maxItems=1,
 *     nullable=true,
 *     @OA\Items(type="string", format="binary"),
 *     description="Person's avatar. jpeg, jpg, png, gif, svg file extensions are supported.",
 * ),
 * @OA\Schema(
 *     schema="SrvMessage",
 *     type="string",
 *     example="Human-readable operation result description.",
 *     description="Human-readable operation result.",
 * ),
 * @OA\Schema(
 *     schema="Password",
 *     type="string",
 *     format="password",
 *     minLength=8,
 *     example="NmmmSdWe428HyTTwerBBnmLop72W",
 *     description="User's password.",
 * ),
 * @OA\Schema(
 *     schema="AccessToken",
 *     type="string",
 *     example="1|wMjg0QkNDU4E0pDRIHftbrGpRMa3aF1YNHbZpMjP",
 *     description="User's access token.",
 * ),
 * @OA\Schema(
 *     schema="AccessTokenType",
 *     type="string",
 *     example="Bearer",
 *     description="Access token type.",
 * ),
 * @OA\Parameter(
 *     name="X-Requested-With",
 *     description="Should be equal to XMLHttpRequest",
 *     in="header",
 *     required=true,
 *     @OA\Schema(type="string", const="XMLHttpRequest"),
 * ),
 * @OA\Parameter(
 *     name="uuid",
 *     description="UUID entry ID",
 *     required=true,
 *     in="path",
 *     @OA\Schema(ref="#/components/schemas/EntryUuid"),
 * ),
 * @OA\Response(
 *     response=400,
 *     description="URL argument is not a valid UUID.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=401,
 *     description="User is not authenticated.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=403,
 *     description="User is not allowed to modify this entry.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=404,
 *     description="There are no entries with this UUID.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=405,
 *     description="Method not allowed for this endpoint.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=409,
 *     description="Entry was already modified by someone else. Please, work with updated version.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=422,
 *     description="Error in values sent to server. Check server reply for more info.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *         @OA\Property(property="errors",
 *             @OA\Property(property="fieldName", type="array", @OA\Items(
 *                 type="string",
 *                 example="Human-readable explanation, what's wrong with provided field value.",
 *             )),
 *         ),
 *     ),
 * ),
 * @OA\Response(
 *     response=429,
 *     description="Too Many Requests. Please, respect x-ratelimit-limit responce header value.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 * @OA\Response(
 *     response=500,
 *     description="Internal Server Error. We'll try to fix it ASAP.",
 *     @OA\JsonContent(
 *         @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
 *     )
 * ),
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
