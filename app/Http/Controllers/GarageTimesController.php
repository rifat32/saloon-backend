<?php

namespace App\Http\Controllers;

use App\Http\Requests\GarageTimesUpdateRequest;
use App\Http\Utils\ErrorUtil;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GarageTimesController extends Controller
{
    use ErrorUtil;
    /**
     *
     * @OA\Patch(
     *      path="/v1.0/garage-times",
     *      operationId="updateGarageTimes",
     *      tags={"garage_times_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update garage times",
     *      description="This method is to update garage times",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"garage_id","times"},
     *    @OA\Property(property="garage_id", type="number", format="number", example="1"),
     *    @OA\Property(property="times", type="string", format="array",example={
     *
    *{"day":0,"opening_time":"10:10:10","closing_time":"10:15:10"},
    *{"day":1,"opening_time":"10:10:10","closing_time":"10:15:10"},
    *{"day":2,"opening_time":"10:10:10","closing_time":"10:15:10"},
     *{"day":3,"opening_time":"10:10:10","closing_time":"10:15:10"},
    *{"day":4,"opening_time":"10:10:10","closing_time":"10:15:10"},
    *{"day":5,"opening_time":"10:10:10","closing_time":"10:15:10"},
    *{"day":6,"opening_time":"10:10:10","closing_time":"10:15:10"}
     *
     * }),

     *
     *         ),
     *      ),
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

    public function updateGarageTimes(GarageTimesUpdateRequest $request)
    {
        try {
            return  DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('fuel_station_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $updatableData = $request->validated();



                $fuel_station  =  tap(FuelStation::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        "name",
                        "address",
                        "opening_time",
                        "closing_time",
                        "description",
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                return response($fuel_station, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }
}
