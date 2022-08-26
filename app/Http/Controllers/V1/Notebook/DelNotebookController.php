<?php

namespace App\Http\Controllers\V1\Notebook;

use App\Models\Notebook;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DelNotebookController extends Controller
{
    /**
     * Delete specified Notebook entry
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     *
     * @OA\Delete(
     *      path="/notebook/{uuid}",
     *      operationId="delNotebookEntry",
     *      tags={"Notebook"},
     *      summary="Delete single notebook",
     *      description="Deletes single notebook",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/uuid"),
     *      @OA\Response(
     *          response=410,
     *          description="Successful deletion.",
     *          @OA\JsonContent(ref="#/components/schemas/DelNotebook410")
     *      ),
     *      @OA\Response(response=400, ref="#/components/responses/400"),
     *      @OA\Response(response=401, ref="#/components/responses/401"),
     *      @OA\Response(response=403, ref="#/components/responses/403"),
     *      @OA\Response(response=404, ref="#/components/responses/404"),
     *      @OA\Response(response=405, ref="#/components/responses/405"),
     *      @OA\Response(response=429, ref="#/components/responses/429"),
     * ),
     * @OA\Schema(
     *     schema="DelNotebook410",
     *     description="Notebook object has been deleted successfully",
     *     @OA\Property(property="id", ref="#/components/schemas/EntryUuid"),
     *     @OA\Property(property="message", ref="#/components/schemas/SrvMessage"),
     * ),
     */
    public function __invoke($id)
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
        if (Auth::user()->id != $notebook['creator_uuid']) {
            return response()->json([
                'message' => 'You are not allowed to delete this entry.',
            ], 403);
        }

        // If there is an entry-associated file
        if ($notebook['picture'] && Storage::exists($notebook['picture'])) {
            Storage::delete($notebook['picture']);
        }

        // Delete entry
        Notebook::destroy($id);

        return response()->json([
            'id' => $notebook['id'],
            'message' => 'Entry deleted.',
        ], 410);
    }
}
