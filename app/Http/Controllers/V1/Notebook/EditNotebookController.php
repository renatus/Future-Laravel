<?php

namespace App\Http\Controllers\V1\Notebook;

use Carbon\Carbon;
use App\Models\Notebook;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\FileService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class EditNotebookController extends Controller
{
    /**
     * Edit specified Notebook entry
     *
     * Works like 'patch', not 'put'. I.E. only explicitely mentioned fields will be updated.
     * Values of absent fields wouldn't be altered.
     * If field value equals 'null', we'll make it empty in DB.
     *
     * @param \Illuminate\Http\Request
     * @param  string  $id                   UUID
     * @return \Illuminate\Http\Response
     *
     * @OA\RequestBody(
     *     request="modNotebookRequest",
     *     description="Notebook object to edit at server",
     *     @OA\MediaType(
     *         mediaType="multipart/form-data",
     *         @OA\Schema(
     *             required={"updated_at"},
     *             @OA\Property(property="updated_at", ref="#/components/schemas/SrvUpdatedAt"),
     *             @OA\Property(property="name", ref="#/components/schemas/Name"),
     *             @OA\Property(property="company", ref="#/components/schemas/Company"),
     *             @OA\Property(property="phone", ref="#/components/schemas/Phone"),
     *             @OA\Property(property="email", ref="#/components/schemas/Email"),
     *             @OA\Property(property="birthday", ref="#/components/schemas/Birthday"),
     *             @OA\Property(property="picture", ref="#/components/schemas/Picture"),
     *         ),
     *     ),
     * ),
     * @OA\Post(
     *      path="/notebook/{uuid}",
     *      operationId="modNotebookEntry",
     *      tags={"Notebook"},
     *      summary="Modify single notebook",
     *      description="Modifies single notebook. Send only those data you need to change.
     *      If you need to nullify given field, send 'null' value.",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/uuid"),
     *      requestBody={"$ref": "#/components/requestBodies/modNotebookRequest"},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation.",
     *          @OA\JsonContent(ref="#/components/schemas/ModNotebook200")
     *      ),
     *      @OA\Response(response=400, ref="#/components/responses/400"),
     *      @OA\Response(response=401, ref="#/components/responses/401"),
     *      @OA\Response(response=403, ref="#/components/responses/403"),
     *      @OA\Response(response=404, ref="#/components/responses/404"),
     *      @OA\Response(response=405, ref="#/components/responses/405"),
     *      @OA\Response(response=409, ref="#/components/responses/409"),
     *      @OA\Response(response=422, ref="#/components/responses/422"),
     *      @OA\Response(response=429, ref="#/components/responses/429"),
     *      @OA\Response(response=500, ref="#/components/responses/500"),
     * ),
     * @OA\Schema(
     *     schema="ModNotebook200",
     *     description="Notebook object has been edited successfully",
     *     @OA\Property(property="id", ref="#/components/schemas/EntryUuid"),
     *     @OA\Property(property="updated_at", ref="#/components/schemas/SrvUpdatedAt"),
     *     @OA\Property(property="picture_url", ref="#/components/schemas/Url"),
     *     @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
     * ),
     */
    public function __invoke(Request $request, $id)
    {
        // If URL argument is not a valid UUID
        if (!Str::isUuid($id)) {
            return response()->json([
                'message' => 'There should be a valid UUID in a URL.',
            ], 400);
        }

        $notebook = Notebook::find($id);

        // If there are no entries with this UUID
        if (!$notebook) {
            return response()->json([
                'message' => 'No such entry.',
            ], 404);
        }

        // If authenticated user is NOT one who've created entry
        if (!Gate::allows('modify-notebook', $notebook)) {
            return response()->json([
                'message' => 'You are not allowed to update this entry.',
            ], 403);
        }

        // Add existing data (if missing) not to get an error on entity editing
        // 'null' values wouldn't be altered
        $request->mergeIfMissing(['name' => $notebook['name']]);
        $request->mergeIfMissing(['company' => $notebook['company']]);
        $request->mergeIfMissing(['phone' => $notebook['phone']]);
        $request->mergeIfMissing(['email' => $notebook['email']]);
        $request->mergeIfMissing(['birthday' => $notebook['birthday']]);

        // Validate client input
        // If client sent wrong data, adequate response is being generated by Laravel
        $validatedData = $request->validate([
            // Should get ISO 8601 datetime, like 2022-07-12T22:16:58.000000Z
            'updated_at' => 'required|date',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'required|string|min:10|max:18',
            'email' => 'required|email:rfc,dns',
            'birthday' => 'nullable|date_format:Y-m-d',
            'picture' => 'nullable|mimes:jpeg,jpg,png,gif,svg|max:15000',
        ]);

        $updatedAtRequest = new Carbon($validatedData['updated_at']);
        // If DB-loaded entry and user-provided entry were last updated at different moments
        if ($updatedAtRequest != $notebook['updated_at']) {
            return response()->json([
                'message' => 'Entry was already modified by someone else. Please, work with updated version.',
            ], 409);
        }

        $fileDbPath = $notebook['picture'];
        // If user have sent a new file
        if ($request->hasFile('picture')) {
            if ($notebook['picture'] && file_exists(FileService::getImgFsPath($notebook['picture']))) {
                // Delete existing file
                // TODO: check why Storage::delete doesn't work at SemaphoreCI VM
                //Storage::delete($notebook['picture']);
                unlink(FileService::getImgFsPath($notebook['picture']));
            }
            $fileDbPath = FileService::requestImgSave($request, $id);
            // Leave this line PSR-2-compliant
        } elseif (array_key_exists('picture', $validatedData) &&
            !$validatedData['picture'] &&
            $notebook['picture'] &&
            file_exists(FileService::getImgFsPath($notebook['picture']))
        ) {
            // If request has 'picture' key with 'null' value, but not missing
            // And there is entry-associated file on a server side
            // Delete existing file
            // TODO: check why Storage::delete doesn't work at SemaphoreCI VM
            //Storage::delete($notebook['picture']);
            unlink(FileService::getImgFsPath($notebook['picture']));
            $fileDbPath = null;
        }

        // Update existing Notebook entry
        $notebook->update([
            'name' => $validatedData['name'],
            'company' => $validatedData['company'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'birthday' => $validatedData['birthday'],
            'picture' => $fileDbPath,
        ]);

        // This responce would be given even if user has sent us no new data,
        // so entry wasn't in fact modified.
        // That's tolerable - client and server data are still synced.
        return response()->json([
            'id' => $notebook['id'],
            'updated_at' => $notebook['updated_at'],
            'picture_url' => $notebook['picture_url'],
            'message' => 'Entry updated.',
        ], 200);
    }
}
