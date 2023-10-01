<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Garage;
use App\Models\GarageAffiliation;
use App\Models\GarageAutomobileMake;
use App\Models\GarageAutomobileModel;
use App\Models\GarageService;
use App\Models\GarageSubService;
use App\Models\ReviewValueNew;
use App\Models\Star;
use App\Models\SubService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientBasicController extends Controller
{
    use ErrorUtil, UserActivityUtil;


    public function getGarageSearchQuery(Request $request) {
        $garagesQuery = Garage::with("owner"
        // "garageAutomobileMakes.automobileMake",
        // "garageAutomobileMakes.garageAutomobileModels.automobileModel",
        // "garageServices.service",
        // "garageServices.garageSubServices.garage_sub_service_prices",
        // "garageServices.garageSubServices.subService",
        // "garage_times",
        // "garageGalleries",
        // "garage_packages",
)
        ->leftJoin('garage_automobile_makes', 'garage_automobile_makes.garage_id', '=', 'garages.id')
        ->leftJoin('garage_automobile_models', 'garage_automobile_models.garage_automobile_make_id', '=', 'garage_automobile_makes.id')

        ->leftJoin('garage_services', 'garage_services.garage_id', '=', 'garages.id')
        ->leftJoin('garage_sub_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')

->leftJoin('garage_times', 'garage_times.garage_id', '=', 'garages.id')
        ;

        if(!empty($request->search_key)) {
            $garagesQuery = $garagesQuery->where(function($query) use ($request){
                $term = $request->search_key;
                $query->where("garages.name", "like", "%" . $term . "%");
                $query->orWhere("garages.phone", "like", "%" . $term . "%");
                $query->orWhere("garages.email", "like", "%" . $term . "%");
                $query->orWhere("garages.city", "like", "%" . $term . "%");
                $query->orWhere("garages.postcode", "like", "%" . $term . "%");
            });

        }







        if (!empty($request->country)) {
            $garagesQuery =   $garagesQuery->where("country", "like", "%" . $request->country . "%");

        }
        if (!empty($request->city)) {
            $garagesQuery =   $garagesQuery->where("city", "like", "%" . $request->city . "%");

        }



        if (!empty($request->is_mobile_garage)) {

            if ($request->is_mobile_garage === '1' || $request->is_mobile_garage === 'true') {
                $garagesQuery = $garagesQuery->where("garages.is_mobile_garage", true);
            }
        }
        if (!empty($request->wifi_available)) {

            if ($request->wifi_available === '1' || $request->wifi_available === 'true') {
                $garagesQuery = $garagesQuery->where("garages.wifi_available", true);
            }
        }



        if(!empty($request->automobile_make_ids)) {
            $null_filter = collect(array_filter($request->automobile_make_ids))->values();
            $automobile_make_ids =  $null_filter->all();
            if(count($automobile_make_ids)) {
                $garagesQuery =   $garagesQuery->whereIn("garage_automobile_makes.automobile_make_id",$automobile_make_ids);
            }

        }
        if(!empty($request->automobile_model_ids)) {

            $null_filter = collect(array_filter($request->automobile_model_ids))->values();
            $automobile_model_ids =  $null_filter->all();
            if(count($automobile_model_ids)) {
                $garagesQuery =   $garagesQuery->whereIn("garage_automobile_models.automobile_model_id",$automobile_model_ids);
            }

        }

        if(!empty($request->service_ids)) {

            $null_filter = collect(array_filter($request->service_ids))->values();
        $service_ids =  $null_filter->all();

            if(count($service_ids)) {
                $garagesQuery =   $garagesQuery->whereIn("garage_services.service_id",$service_ids);
            }

        }


        if(!empty($request->sub_service_ids)) {
            $null_filter = collect(array_filter($request->sub_service_ids))->values();
        $sub_service_ids =  $null_filter->all();
            if(count($sub_service_ids)) {
                $garagesQuery =   $garagesQuery->whereIn("garage_sub_services.sub_service_id",$sub_service_ids);
            }

        }

        if (!empty($request->start_lat)) {
            $garagesQuery = $garagesQuery->where('lat', ">=", $request->start_lat);
        }
        if (!empty($request->end_lat)) {
            $garagesQuery = $garagesQuery->where('lat', "<=", $request->end_lat);
        }
        if (!empty($request->start_long)) {
            $garagesQuery = $garagesQuery->where('long', ">=", $request->start_long);
        }
        if (!empty($request->end_long)) {
            $garagesQuery = $garagesQuery->where('long', "<=", $request->end_long);
        }
        if (!empty($request->date_time)) {
            $date = Carbon::createFromFormat('Y-m-d H:i', $request->date_time);
            $dayOfWeek = $date->dayOfWeek; // 6 (0 for Sunday, 1 for Monday, 2 for Tuesday, etc.)
            $time = $date->format('H:i');
            $garagesQuery = $garagesQuery->where('garage_times.day', "=", $dayOfWeek)
            ->whereTime('garage_times.opening_time', "<=", $time)
            ->whereTime('garage_times.closing_time', ">", $time);
        }

        return $garagesQuery;

    }



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
* name="country",
* in="query",
* description="country",
* required=true,
* example="country"
* ),
     * *  @OA\Parameter(
* name="address",
* in="query",
* description="address",
* required=true,
* example="address"
* ),
     * *  @OA\Parameter(
* name="city",
* in="query",
* description="city",
* required=true,
* example="city"
* ),
    * *  @OA\Parameter(
* name="start_lat",
* in="query",
* description="start_lat",
* required=true,
* example="3"
* ),
     * *  @OA\Parameter(
* name="end_lat",
* in="query",
* description="end_lat",
* required=true,
* example="2"
* ),
     * *  @OA\Parameter(
* name="start_long",
* in="query",
* description="start_long",
* required=true,
* example="1"
* ),
     * *  @OA\Parameter(
* name="end_long",
* in="query",
* description="end_long",
* required=true,
* example="4"
* ),

*  @OA\Parameter(
*      name="automobile_make_ids[]",
*      in="query",
*      description="automobile_make_ids",
*      required=true,
*      example="1,2"
* ),
*  @OA\Parameter(
*      name="automobile_model_ids[]",
*      in="query",
*      description="automobile_model_id",
*      required=true,
*      example="1,2"
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
     * *  @OA\Parameter(
* name="wifi_available",
* in="query",
* description="wifi_available",
* required=true,
* example="1"
* ),
     * *  @OA\Parameter(
* name="is_mobile_garage",
* in="query",
* description="is_mobile_garage",
* required=true,
* example="1"
* ),

     * *  @OA\Parameter(
* name="date_time",
* in="query",
* description="2019-06-29 22:00",
* required=true,
* example="1"
* ),
*
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
            $this->storeActivity($request,"");

            $info=[];


            if(!empty($request->address)) {
                $garages = $this->getGarageSearchQuery($request)
                ->where("garages.city",$request->address)
                ->groupBy("garages.id")

                ->orderByDesc("garages.id")
                ->select("garages.*")
                ->paginate($perPage);

                $info["is_result_by_city"] = true;
                $info["is_result_by_country"] = false;

                if (count($garages->items()) == 0) {
                    $info["is_result_by_city"] = false;
                    $info["is_result_by_country"] = true;

                    $garages = $this->getGarageSearchQuery($request)
                    ->where("garages.country",$request->address)
                    ->groupBy("garages.id")

                    ->orderByDesc("garages.id")
                    ->select(
                        "garages.*",
                        DB::raw('CASE
                        WHEN (SELECT COUNT(garage_packages.id) FROM garage_packages WHERE garage_packages.garage_id = garages.id) = 0 THEN 0
                        ELSE 1
                    END AS is_package_available')
                        )
                    ->paginate($perPage);



                }
                if (count($garages->items()) == 0) {
                    $info["is_result_by_city"] = false;
                    $info["is_result_by_country"] = false;
                }

            }
            else {

                array_splice($info, 0);

                    $garages = $this->getGarageSearchQuery($request)
           

                    ->groupBy("garages.id")

                    ->orderByDesc("garages.id")
                    ->select("garages.*",
                    DB::raw('CASE
                    WHEN (SELECT COUNT(garage_packages.id) FROM garage_packages WHERE garage_packages.garage_id = garages.id) = 0 THEN 0
                    ELSE 1
                END AS is_package_available')

                    )

                    ->paginate($perPage);

                }


foreach($garages->items() as $key=>$value) {
    $totalCount = 0;
$ttotalRating = 0;

foreach(Star::get() as $star) {

    $data2["star_" . $star->value . "_selected_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
    ->where([
        "review_news.garage_id" => $garages->items()[$key]->id,
        "star_id" => $star->id,
        // "review_news.guest_id" => NULL
    ])
    ->distinct("review_value_news.review_id","review_value_news.question_id");
    if(!empty($request->start_date) && !empty($request->end_date)) {

        $data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->whereBetween('review_news.created_at', [
            $request->start_date,
            $request->end_date
        ]);

    }
    $data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->count();

    $totalCount += $data2["star_" . $star->value . "_selected_count"] * $star->value;

    $ttotalRating += $data2["star_" . $star->value . "_selected_count"];

}
if($totalCount > 0) {
    $data2["total_rating"] = $totalCount / $ttotalRating;

}
else {
    $data2["total_rating"] = 0;

}
$garages->items()[$key]->average_rating = $data2["total_rating"];

}



            return response()->json([
                "info"=>$info,

                "data"=>$garages], 200);
        } catch(Exception $e){

            return $this->sendError($e,500,$request);
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
            $this->storeActivity($request,"");
            $garagesQuery = Garage::with(
                "owner",
                "garageAutomobileMakes.automobileMake",
                "garageAutomobileMakes.garageAutomobileModels.automobileModel",
                "garageServices.service",
                "garageServices.garageSubServices.garage_sub_service_prices",
                "garageServices.garageSubServices.subService",
                "garage_times",
                "garageGalleries",
                "garage_packages",
                "garage_affiliations.affiliation"

            );



            $garage = $garagesQuery->where([
                "id" => $id
            ])
            ->first();


            if(!$garage) {


           return response()->json([
            "message" => "no garage found"
           ],404);

            }


       $garage_automobile_make_ids =  GarageAutomobileMake::where(["garage_id"=>$garage->id])->pluck("automobile_make_id");
        $garage_service_ids =   GarageService::where(["garage_id"=>$garage->id])->pluck("service_id");

        $data["garage"] = $garage;
        $data["garage_automobile_make_ids"] = $garage_automobile_make_ids;
        $data["garage_service_ids"] = $garage_service_ids;



        $totalCount = 0;
        $ttotalRating = 0;

        foreach(Star::get() as $star) {

            $data2["star_" . $star->value . "_selected_count"] = ReviewValueNew::leftjoin('review_news', 'review_value_news.review_id', '=', 'review_news.id')
            ->where([
                "review_news.garage_id" => $garage->id,
                "star_id" => $star->id,
                // "review_news.guest_id" => NULL
            ])
            ->distinct("review_value_news.review_id","review_value_news.question_id");
            if(!empty($request->start_date) && !empty($request->end_date)) {

                $data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->whereBetween('review_news.created_at', [
                    $request->start_date,
                    $request->end_date
                ]);

            }
            $data2["star_" . $star->value . "_selected_count"] = $data2["star_" . $star->value . "_selected_count"]->count();

            $totalCount += $data2["star_" . $star->value . "_selected_count"] * $star->value;

            $ttotalRating += $data2["star_" . $star->value . "_selected_count"];

        }
        if($totalCount > 0) {
            $data2["total_rating"] = $totalCount / $ttotalRating;

        }
        else {
            $data2["total_rating"] = 0;

        }
        $data["garage"]->average_rating = $data2["total_rating"];


        return response()->json($data, 200);
        } catch(Exception $e){

            return $this->sendError($e,500,$request);
        }

    }
  /**
        *
     * @OA\Get(
     *      path="/v1.0/client/garages/service-model-details/{garage_id}",
     *      operationId="getGarageServiceModelDetailsByIdClient",
     *      tags={"client.basics"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to get garage service-model-details by garage id by id",
     *      description="This method is to get garage service-model-details by garage id by id",
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

    public function getGarageServiceModelDetailsByIdClient($garage_id,Request $request) {

        try{
            $this->storeActivity($request,"");
            $garage = Garage::where([
                "id" => $garage_id
            ])->first();


            if(!$garage) {


           return response()->json([
            "message" => "no garage found"
           ],404);

            }
$data["garage_services"] = GarageService::with("service")
->where([
    "garage_id" => $garage->id
])
->get();

$data["garage_sub_services"] = GarageSubService::with("subService")
->leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
->where([
    "garage_services.garage_id" => $garage->id
])
->select(
    "garage_sub_services.*"
)

->get();

$data["garage_automobile_makes"] = GarageAutomobileMake::with("automobileMake")
->where([
    "garage_id" => $garage->id
])
->get();

$data["garage_automobile_models"] = GarageAutomobileModel::with("automobileModel")
->leftJoin('garage_automobile_makes', 'garage_automobile_models.garage_automobile_make_id', '=', 'garage_automobile_makes.id')
->where([
    "garage_automobile_makes.garage_id" => $garage->id
])
->select(
    "garage_automobile_models.*"
)
->get();




        return response()->json($data, 200);
        } catch(Exception $e){

            return $this->sendError($e,500,$request);
        }

    }
  /**
        *
     * @OA\Get(
     *      path="/v1.0/client/garages/garage-automobile-models/{garage_id}/{automobile_make_id}",
     *      operationId="getGarageAutomobileModelsByAutomobileMakeId",
     *      tags={"client.basics"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="1"
     *      ),
     *   *              @OA\Parameter(
     *         name="automobile_make_id",
     *         in="path",
     *         description="automobile_make_id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to get garage service-model-details by garage id by id",
     *      description="This method is to get garage service-model-details by garage id by id",
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

    public function getGarageAutomobileModelsByAutomobileMakeId($garage_id,$automobile_make_id,Request $request) {

        try{
            $this->storeActivity($request,"");
            $garage = Garage::where([
                "id" => $garage_id
            ])->first();


            if(!$garage) {

           return response()->json([
            "message" => "no garage found"
           ],404);

            }
$data = GarageAutomobileModel::with("automobileModel")
->leftJoin('garage_automobile_makes', 'garage_automobile_models.garage_automobile_make_id', '=', 'garage_automobile_makes.id')
->where([
    "garage_automobile_makes.automobile_make_id" => $automobile_make_id,
    "garage_automobile_makes.garage_id" => $garage->id
])
->select(
    "garage_automobile_models.*"
)
->get();




        return response()->json($data, 200);
        } catch(Exception $e){

            return $this->sendError($e,500,$request);
        }

    }





 /**
     *
     * @OA\Get(
     *      path="/v1.0/client/garage-affiliations/get/all/{garage_id}",
     *      operationId="getGarageAffiliationsAllByGarageIdClient",
     *      tags={"client.basics"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
*              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="1"
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
     *      summary="This method is to get garage affiliations ",
     *      description="This method is to get garage affiliations",
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

    public function getGarageAffiliationsAllByGarageIdClient($garage_id, Request $request)
    {
        try {
            $this->storeActivity($request,"");










            // $automobilesQuery = AutomobileMake::with("makes");

            $affiliationQuery =  GarageAffiliation::with("affiliation","garage")
            ->leftJoin('affiliations', 'affiliations.id', '=', 'garage_affiliations.affiliation_id')
            ->where([
                "garage_id" => $garage_id
            ]);

            if (!empty($request->search_key)) {
                $affiliationQuery = $affiliationQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("affiliations.name", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $affiliationQuery = $affiliationQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $affiliationQuery = $affiliationQuery->where('created_at', "<=", $request->end_date);
            }

            $affiliations = $affiliationQuery->orderByDesc("garage_affiliations.id")->get();


            return response()->json($affiliations, 200);
        } catch (Exception $e) {

            return $this->sendError($e,500,$request);
        }
    }








  /**
        *
     * @OA\Get(
     *      path="/v1.0/client/favourite-sub-services/{perPage}",
     *      operationId="getFavouriteSubServices",
     *      tags={"client.basics"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *   *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),

     *      summary="This method is to get favourite jobs",
     *      description="This method is to get favourite jobs",
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

    public function getFavouriteSubServices($perPage,Request $request) {

        try{
            $this->storeActivity($request,"");
            $user = $request->user();
            $data = SubService::
            select("sub_services.*",
            DB::raw('(SELECT COUNT(job_sub_services.sub_service_id)
            FROM
            job_sub_services
            LEFT JOIN jobs ON job_sub_services.job_id = jobs.id


            WHERE jobs.customer_id = '
            .
            $user->id
            .
            '
            AND
            job_sub_services.sub_service_id = sub_services.id

            ) AS sub_service_id_count'),
            )
            ->orderByRaw('sub_service_id_count desc')
            ->havingRaw('sub_service_id_count > 0')
            ->paginate($perPage);








        return response()->json($data, 200);
        } catch(Exception $e){
            return $this->sendError($e,500,$request);
        }

    }




}
