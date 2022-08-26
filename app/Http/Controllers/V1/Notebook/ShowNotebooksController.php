<?php

namespace App\Http\Controllers\V1\Notebook;

use App\Models\Notebook;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowNotebooksController extends Controller
{
    /**
     * Display all Notebook entries, paginated
     *
     * User may specify, how much results per page they would prefer, within certain range.
     * Range and default value are being set at .env file.
     * Wrong page number and/or cursor value would be omitted, not resulting in 422 error.
     *
     * @OA\Get(
     *      path="/notebook",
     *      operationId="getNotebooksList",
     *      tags={"Notebook"},
     *      summary="Get list of notebooks",
     *      description="Returns list of notebooks",
     *      @OA\Parameter(ref="#/components/parameters/X-Requested-With"),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Desired number of entries per page, between 3 and 100",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              minimum=3,
     *              maximum=100,
     *              example="25"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="cursor",
     *          description="Cursor for cursor pagination",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              example="eyJub3RlYm9va3MuaWQiOiI5NmUxY2I4YS1jMzVmLTRkZjAtOWI3Ni02MjlmNDNjZTFkNDUiLCJfcG9pbnRzVG9OZXh0SXRlbXMiOnRydWV9"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/GetNotebooks200")
     *      ),
     *      @OA\Response(response=405, ref="#/components/responses/405"),
     *      @OA\Response(response=429, ref="#/components/responses/429"),
     * ),
     * @OA\Schema(
     *     schema="GetNotebooks200",
     *     description="Notebook object has been returned successfully",
     *     @OA\Property(property="data", type="array", @OA\Items({
     *         @OA\Property(property="id", ref="#/components/schemas/EntryUuid"),
     *         @OA\Property(property="creator_uuid", ref="#/components/schemas/EntryUuid"),
     *         @OA\Property(property="name", ref="#/components/schemas/Name"),
     *         @OA\Property(property="company", ref="#/components/schemas/Company"),
     *         @OA\Property(property="phone", ref="#/components/schemas/Phone"),
     *         @OA\Property(property="email", ref="#/components/schemas/Email"),
     *         @OA\Property(property="birthday", ref="#/components/schemas/Birthday"),
     *         @OA\Property(property="created_at", ref="#/components/schemas/SrvCreatedAt"),
     *         @OA\Property(property="updated_at", ref="#/components/schemas/SrvUpdatedAt"),
     *         @OA\Property(property="picture_url", ref="#/components/schemas/Url"),
     *     })),
     *     @OA\Property(property="path", ref="#/components/schemas/GetNotebooksPath"),
     *     @OA\Property(property="per_page", ref="#/components/schemas/GetNotebooksPerPage"),
     *     @OA\Property(property="next_cursor", ref="#/components/schemas/GetNotebooksNCursor"),
     *     @OA\Property(property="next_page_url", ref="#/components/schemas/GetNotebooksNPage"),
     *     @OA\Property(property="prev_cursor", ref="#/components/schemas/GetNotebooksPCursor"),
     *     @OA\Property(property="prev_page_url", ref="#/components/schemas/GetNotebooksPPage"),
     * ),
     * @OA\Schema(
     *     schema="GetNotebooksPath",
     *     type="string",
     *     format="url",
     *     example="http://localhost/api/v1/notebook",
     *     description="Endpoint URL.",
     * ),
     * @OA\Schema(
     *     schema="GetNotebooksPerPage",
     *     type="integer",
     *     format="int32",
     *     example="25",
     *     description="Desired number of entries per page, between 3 and 100.",
     * ),
     * @OA\Schema(
     *     schema="GetNotebooksNCursor",
     *     type="string|null",
     *     example="eyJub3RlYm9va3MuaWQiOiI5NzE4NTZhMi1mNTkzLTRiOGQtOTc5Yi05YzNmNWRmMzMyMjEiLCJfcG9pbnRzVG9OZXh0SXRlbXMiOnRydWV9",
     *     description="Next cursor, for cursor pagination.",
     * ),
     * @OA\Schema(
     *     schema="GetNotebooksNPage",
     *     type="string|null",
     *     format="url",
     *     example="http://localhost/api/v1/notebook?cursor=eyJub3RlYm9va3MuaWQiOiI5NzE4NTZhMi1mNTkzLTRiOGQtOTc5Yi05YzNmNWRmMzMyMjEiLCJfcG9pbnRzVG9OZXh0SXRlbXMiOnRydWV9",
     *     description="Next page URL.",
     * ),
     * @OA\Schema(
     *     schema="GetNotebooksPCursor",
     *     type="string|null",
     *     example="eyJub3RlYm9va3MuaWQiOiI5NzE4MWFmNC0wNzE1LTRmYTAtOTE2Zi1kNzdkYTdmYTg0ZTMiLCJfcG9pbnRzVG9OZXh0SXRlbXMiOmZhbHNlfQ",
     *     description="Previous cursor, for cursor pagination.",
     * ),
     * @OA\Schema(
     *     schema="GetNotebooksPPage",
     *     type="string|null",
     *     format="url",
     *     example="http://localhost/api/v1/notebook?cursor=eyJub3RlYm9va3MuaWQiOiI5NzE4MWFmNC0wNzE1LTRmYTAtOTE2Zi1kNzdkYTdmYTg0ZTMiLCJfcG9pbnRzVG9OZXh0SXRlbXMiOmZhbHNlfQ",
     *     description="Previous page URL.",
     * ),
     */
    public function __invoke(Request $request)
    {
        $perPage = $_ENV['FUTURE_PAGINATION_DEF'];
        // Leave this line PSR-2-compliant
        if (is_int(intval($request['per_page'])) &&
            $_ENV['FUTURE_PAGINATION_MIN'] <= $request['per_page'] &&
            $_ENV['FUTURE_PAGINATION_MAX'] >= $request['per_page']
        ) {
            $perPage = $request['per_page'];
        }

        return Notebook::cursorPaginate($perPage);
    }
}
