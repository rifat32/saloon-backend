<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\GarageService;
use App\Models\GarageSubService;
use Exception;
use Illuminate\Http\Request;

class GarageServiceController extends Controller
{
    use ErrorUtil,GarageUtil,UserActivityUtil;
   /**
        *
     * @OA\Get(
     *      path="/v1.0/garage-services/{garage_id}/{perPage}",
     *      operationId="getGarageServices",
     *      tags={"garage_service_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },

         *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="6"
     *      ),
     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),

     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),


     *      summary="This method is to get automobile Services ",
     *      description="This method is to get automobile Services",
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

    public function getGarageServices($garage_id,$perPage,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('garage_services_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           if (!$this->garageOwnerCheck($garage_id)) {
            return response()->json([
                "message" => "you are not the owner of the garage or the requested garage does not exist."
            ], 401);
        }

            // $automobilesQuery = AutomobileMake::with("makes");

            $servicesQuery = GarageService::with("service")
            ->leftJoin('services', 'garage_services.service_id', '=', 'services.id');


            if(!empty($request->search_key)) {
                $servicesQuery = $servicesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("services.name", "like", "%" . $term . "%");
                });

            }









            $services = $servicesQuery
            ->select("garage_services.*")
            ->orderByDesc("garage_services.id")->paginate($perPage);
            return response()->json($services, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }





      /**
        *
     * @OA\Get(
     *      path="/v1.0/garage-sub-services/{garage_id}/{garage_service_id}/{perPage}",
     *      operationId="getGarageSubServices",
     *      tags={"garage_service_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },

         *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="6"
     *      ),
     *      *              @OA\Parameter(
     *         name="garage_service_id",
     *         in="path",
     *         description="garage_service_id",
     *         required=true,
     *  example="6"
     *      ),
     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),

     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),


     *      summary="This method is to get automobile Services ",
     *      description="This method is to get automobile Services",
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

    public function getGarageSubServices($garage_id,$garage_service_id,$perPage,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('garage_services_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           if (!$this->garageOwnerCheck($garage_id)) {
            return response()->json([
                "message" => "you are not the owner of the garage or the requested garage does not exist."
            ], 401);
        }

            // $automobilesQuery = AutomobileMake::with("makes");

            $servicesQuery = GarageSubService::with("subService")
            ->leftJoin('sub_services', 'garage_sub_services.sub_service_id', '=', 'sub_services.id')
            ->leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
            ->where([
                "garage_services.id" => $garage_service_id,
                "garage_services.garage_id" => $garage_id
            ])
            ;

            if(!empty($request->search_key)) {
                $servicesQuery = $servicesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("sub_services.name", "like", "%" . $term . "%");
                });

            }


            $services = $servicesQuery
            ->select("garage_sub_services.*")
            ->orderByDesc("garage_sub_services.id")->paginate($perPage);
            return response()->json($services, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }
}
