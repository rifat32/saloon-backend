<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Utils\ErrorUtil;
use App\Models\Garage;
use Exception;
use Illuminate\Http\Request;

class ClientBasicController extends Controller
{
    use ErrorUtil;
    /**
        *
     * @OA\Get(
     *      path="/v1.0/client/garages/{perPage}",
     *      operationId="getGaragesClient",
     *      tags={"client.basics"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to get garages by client",
     *      description="This method is to get garages by client",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function getGaragesClient($perPage,Request $request) {

        try{
            $garagesQuery = Garage::with("owner");
            if(!empty($request->search_key)) {
                $garagesQuery = $garagesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                    $query->orWhere("phone", "like", "%" . $term . "%");
                    $query->orWhere("email", "like", "%" . $term . "%");
                    $query->orWhere("city", "like", "%" . $term . "%");
                    $query->orWhere("postcode", "like", "%" . $term . "%");
                });

            }



            $garages = $garagesQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($garages, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }
}
