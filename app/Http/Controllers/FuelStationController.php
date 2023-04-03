<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuelStationCreateRequest;
use App\Http\Requests\FuelStationUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Models\FuelStation;
use App\Models\FuelStationOption;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelStationController extends Controller
{
    use ErrorUtil;

    /**
     *
     * @OA\Post(
     *      path="/v1.0/fuel-station",
     *      operationId="createFuelStation",
     *      tags={"fuel_station_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store fuel station",
     *      description="This method is to store fuel station",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","address","opening_time","closing_time","description"},
     *    @OA\Property(property="name", type="string", format="string",example="car"),
     *    @OA\Property(property="address", type="string", format="string",example="car"),
     *    @OA\Property(property="opening_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="closing_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="description", type="string", format="number",example="description"),
     *    *  * *    @OA\Property(property="lat", type="string", format="string",example="1"),
     *  * *    @OA\Property(property="long", type="string", format="string",example="1"),
     *
     * *  * *    @OA\Property(property="options", type="string", format="array",example={
     * {"option":"toilet","is_active":true},
     *  * {"option":"cng","is_active":true},
     * }),
     *
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

    public function createFuelStation(FuelStationCreateRequest $request)
    {
        try {

            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('fuel_station_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $insertableData = $request->validated();

                $fuel_station =  FuelStation::create($insertableData);

                if(empty($insertableData["options"])) {
                    $insertableData["options"]  = [];
                }

                foreach($insertableData["options"] as $option) {

                    FuelStationOption::create([
                        "fuel_station_id"=>$fuel_station->id,
                        "option"=> $option["option"],
                        "is_active"=> $option["is_active"],
                    ]);

                }

                return response($fuel_station, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/fuel-station",
     *      operationId="updateFuelStation",
     *      tags={"fuel_station_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update fuel station",
     *      description="This method is to update fuel station",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name","address","opening_time","closing_time","description"},
     *    @OA\Property(property="id", type="number", format="number", example="1"),
     *    @OA\Property(property="name", type="string", format="string",example="car"),
     *    @OA\Property(property="address", type="string", format="string",example="car"),
     *    @OA\Property(property="opening_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="closing_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="description", type="string", format="number",example="description"),
     *
     *  *    *  * *    @OA\Property(property="lat", type="string", format="string",example="1"),
     *  * *    @OA\Property(property="long", type="string", format="string",example="1"),
     *
     *   * *  * *    @OA\Property(property="options", type="string", format="array",example={
     * {"option":"toilet","is_active":true},
     *  * {"option":"cng","is_active":true},
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

    public function updateFuelStation(FuelStationUpdateRequest $request)
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
                        "lat",
                        "long",
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                    if(!$fuel_station){
                        return response()->json([
                            "message" => "no fuel station found"
                        ],
                        404);
                    }


                    FuelStationOption::where([
                        "fuel_station_id"=>$fuel_station->id,
                    ])
                    ->delete();


                    if(empty($updatableData["options"])) {
                        $updatableData["options"]  = [];
                    }

                    foreach($updatableData["options"] as $option) {

                        FuelStationOption::create([
                            "fuel_station_id"=>$fuel_station->id,
                            "option"=> $option["option"],
                            "is_active"=> $option["is_active"],
                        ]);

                    }




                return response($fuel_station, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/fuel-station/{perPage}",
     *      operationId="getFuelStations",
     *      tags={"fuel_station_management"},
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
     *      * *  @OA\Parameter(
* name="start_date",
* in="query",
* description="start_date",
* required=true,
* example="2019-06-29"
* ),
     * *  @OA\Parameter(
* name="end_date",
* in="query",
* description="end_date",
* required=true,
* example="2019-06-29"
* ),
     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     * *  @OA\Parameter(
* name="start_lat",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     * *  @OA\Parameter(
* name="end_lat",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     * *  @OA\Parameter(
* name="start_long",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     * *  @OA\Parameter(
* name="end_long",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
*  @OA\Parameter(
*      name="active_options[]",
*      in="query",
*      description="active_options",
*      required=true,
*      example="toilet,cng"
* ),
     *      summary="This method is to get fuel stations ",
     *      description="This method is to get fuel stations",
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

    public function getFuelStations($perPage, Request $request)
    {
        try {
            // if (!$request->user()->hasPermissionTo('fuel_station_view')) {
            //     return response()->json([
            //         "message" => "You can not perform this action"
            //     ], 401);
            // }

            // $automobilesQuery = AutomobileMake::with("makes");

            $fuelStationQuery = FuelStation::leftJoin('fues_station_options', 'fues_station_options.fuel_station_id', '=', 'fuel_stations.id');

            if (!empty($request->search_key)) {
                $fuelStationQuery = $fuelStationQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("fuel_stations.name", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.created_at', "<=", $request->end_date);
            }

            if (!empty($request->start_lat)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', ">=", $request->start_lat);
            }
            if (!empty($request->end_lat)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', "<=", $request->end_lat);
            }
            if (!empty($request->start_long)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', ">=", $request->start_long);
            }
            if (!empty($request->end_long)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', "<=", $request->end_long);
            }

            if(!empty($request->active_options)) {
                if(count($request->active_options)) {
                    $fuelStationQuery =   $fuelStationQuery
                    ->whereIn("fuel_station_options.option",
                    $request->active_options)



                    ->whereIn("fuel_station_options.is_active",$request->active_options)
                    ;
                }

            }

            $fuelStations = $fuelStationQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($fuelStations, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/fuel-station/{id}",
     *      operationId="deleteFuelStationById",
     *      tags={"fuel_station_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to delete fuel station by id",
     *      description="This method is to delete fuel station by id",
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

    public function deleteFuelStationById($id, Request $request)
    {

        try {
            if (!$request->user()->hasPermissionTo('fuel_station_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            FuelStation::where([
                "id" => $id
            ])
                ->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500);
        }
    }
}
