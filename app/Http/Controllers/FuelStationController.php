<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuelStationCreateRequest;
use App\Http\Requests\FuelStationUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Models\FuelStation;
use Exception;
use Illuminate\Http\Request;

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
     *    @OA\Property(property="opening_time", type="string", format="string",example="2019-06-29"),
     * *    @OA\Property(property="closing_time", type="string", format="string",example="2019-06-29"),
     * *    @OA\Property(property="description", type="string", format="number",example="description"),
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
        try{
            if(!$request->user()->hasPermissionTo('fuel_station_create')){
                 return response()->json([
                    "message" => "You can not perform this action"
                 ],401);
            }

            $insertableData = $request->validated();

            $fuel_station =  FuelStation::create($insertableData);


            return response($fuel_station, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
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
     *    @OA\Property(property="opening_time", type="string", format="string",example="2019-06-29"),
     * *    @OA\Property(property="closing_time", type="string", format="string",example="2019-06-29"),
     * *    @OA\Property(property="description", type="string", format="number",example="description"),
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

        try{
            if(!$request->user()->hasPermissionTo('fuel_station_update')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            $updatableData = $request->validated();



                $fuel_station  =  tap(FuelStation::where(["id" => $updatableData["id"]]))->update(collect($updatableData)->only([
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
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
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

    public function getFuelStations($perPage,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('fuel_station_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            // $automobilesQuery = AutomobileMake::with("makes");

            $fuelStationQuery = new FuelStation();

            if(!empty($request->search_key)) {
                $fuelStationQuery = $fuelStationQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                });

            }

            if(!empty($request->start_date) && !empty($request->end_date)) {
                $fuelStationQuery = $fuelStationQuery->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);

            }

            $fuelStations = $fuelStationQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($fuelStations, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
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

    public function deleteFuelStationById($id,Request $request) {

        try{
            if(!$request->user()->hasPermissionTo('fuel_station_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           FuelStation::where([
            "id" => $id
           ])
           ->delete();

            return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }




}
