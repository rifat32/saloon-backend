<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Utils\ErrorUtil;
use App\Models\Garage;
use App\Models\GarageAutomobileMake;
use App\Models\GarageService;
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

     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     * *  @OA\Parameter(
* name="country_code",
* in="query",
* description="country_code",
* required=true,
* example="country_code"
* ),
     * *  @OA\Parameter(
* name="city",
* in="query",
* description="city",
* required=true,
* example="city"
* ),
     * *  @OA\Parameter(
* name="make_id",
* in="query",
* description="automobile_make_id",
* required=true,
* example="1"
* ),
     * *  @OA\Parameter(
* name="make_id",
* in="query",
* description="automobile_model_id",
* required=true,
* example="1"
* ),
*  @OA\Parameter(
*      name="service_ids[]",
*      in="query",
*      description="service_id",
*      required=true,
*      example="1,2"
* ),
*  @OA\Parameter(
*      name="sub_service_ids[]",
*      in="query",
*      description="sub_service_id",
*      required=true,
*      example="1,2"
* ),

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
            $garagesQuery = Garage::with("owner")
            ->leftJoin('garage_automobile_makes', 'garage_automobile_makes.garage_id', '=', 'garages.id')
            ->leftJoin('garage_automobile_models', 'garage_automobile_models.garage_automobile_make_id', '=', 'garage_automobile_makes.id')

            ->leftJoin('garage_services', 'garage_services.garage_id', '=', 'garages.id')
            ->leftJoin('garage_sub_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
            ;
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

            if (!empty($request->country_code)) {
                $garagesQuery =   $garagesQuery->where("country", "like", "%" . $request->country_code . "%");

            }
            if (!empty($request->city)) {
                $garagesQuery =   $garagesQuery->where("city", "like", "%" . $request->city . "%");

            }

            if (!empty($request->automobile_make_id)) {
                $garagesQuery =   $garagesQuery->where("garage_automobile_makes.automobile_make_id",$request->automobile_make_id);

            }
            if (!empty($request->automobile_model_id)) {
                $garagesQuery =   $garagesQuery->where("garage_automobile_models.automobile_model_id",$request->automobile_model_id);
            }
            if(!empty($request->service_ids)) {
                if(count($request->service_ids)) {
                    $garagesQuery =   $garagesQuery->whereIn("garage_services.service_id",$request->service_ids);
                }

            }


            if(!empty($request->sub_service_ids)) {
                if(count($request->sub_service_ids)) {
                    $garagesQuery =   $garagesQuery->whereIn("garage_sub_services.sub_service_id",$request->sub_service_ids);
                }

            }



            $garages = $garagesQuery

            ->distinct("garages.id")

            ->orderByDesc("garages.id")
            ->select("garages.*")

            ->paginate($perPage);
            return response()->json($garages, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }


     /**
        *
     * @OA\Get(
     *      path="/v1.0/client/garages/single/{id}",
     *      operationId="getGarageByIdClient",
     *      tags={"client.basics"},
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
     *      summary="This method is to get garage by id",
     *      description="This method is to get garage by id",
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

    public function getGarageByIdClient($id,Request $request) {

        try{
            $garagesQuery = Garage::with(
                "owner",
                "garageAutomobileMakes.garageAutomobileModels",
                "garageServices.garageSubServices.garage_sub_service_prices",
                "garage_times",
                "garageGalleries",

            );



            $garage = $garagesQuery->where([
                "id" => $id
            ])
            ->first();
       $garage_automobile_make_ids =  GarageAutomobileMake::where(["garage_id"=>$garage->id])->pluck("automobile_make_id");
        $garage_service_ids =   GarageService::where(["garage_id"=>$garage->id])->pluck("service_id");

        $data["garage"] = $garage;
        $data["garage_automobile_make_ids"] = $garage_automobile_make_ids;
        $data["garage_service_ids"] = $garage_service_ids;
        return response()->json($data, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }

    }











}
