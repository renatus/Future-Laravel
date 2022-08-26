<?php

namespace App\Http\Controllers\V1\Notebook;

use App\Models\Notebook;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class ShowNotebookController extends Controller
{
    /**
     * Display specified Notebook entry
     *
     * @param  string  $id
     *
     * @OA\Get(
     *      path="/notebook/{uuid}",
     *      operationId="getNotebookEntry",
     *      tags={"Notebook"},
     *      summary="Get single notebook",
     *      description="Returns single notebook",
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(ref="#/components/parameters/uuid"),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/GetNotebook200")
     *      ),
     *      @OA\Response(response=400, ref="#/components/responses/400"),
     *      @OA\Response(response=404, ref="#/components/responses/404"),
     *      @OA\Response(response=405, ref="#/components/responses/405"),
     *      @OA\Response(response=429, ref="#/components/responses/429"),
     * ),
     * @OA\Schema(
     *     schema="GetNotebook200",
     *     description="Notebook object has been returned successfully",
     *     @OA\Property(property="id", ref="#/components/schemas/EntryUuid"),
     *     @OA\Property(property="creator_uuid", ref="#/components/schemas/EntryUuid"),
     *     @OA\Property(property="name", ref="#/components/schemas/Name"),
     *     @OA\Property(property="company", ref="#/components/schemas/Company"),
     *     @OA\Property(property="phone", ref="#/components/schemas/Phone"),
     *     @OA\Property(property="email", ref="#/components/schemas/Email"),
     *     @OA\Property(property="birthday", ref="#/components/schemas/Birthday"),
     *     @OA\Property(property="created_at", ref="#/components/schemas/SrvCreatedAt"),
     *     @OA\Property(property="updated_at", ref="#/components/schemas/SrvUpdatedAt"),
     *     @OA\Property(property="picture_url", ref="#/components/schemas/Url"),
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

        return $notebook;
    }
}
