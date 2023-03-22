<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\PriceUtil;
use App\Models\GarageSubService;
use App\Models\PreBooking;
use Exception;
use Illuminate\Http\Request;

class JobBidController extends Controller
{
    use ErrorUtil,GarageUtil,PriceUtil;
   /**
        *
     * @OA\Get(
     *      path="/v1.0/pre-bookings/{garage_id}/{perPage}",
     *      operationId="getPreBookings",
     *      tags={"pre_booking_management"},
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
     *      summary="This method is to get pre bookings ",
     *      description="This method is to get pre bookings by garage id. only supported prebooking will show. the garage must have the sub service selected in the pre booking",
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

    public function getPreBookings($garage_id,$perPage,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('job_bids_create')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }

            $garage_sub_service_ids = GarageSubService::
            leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
            ->where([
                "garage_services.garage_id" => $garage_id
            ])
            ->pluck("garage_sub_services.sub_service_id");

            $preBookingQuery = PreBooking::with("pre_booking_sub_services.sub_service")
            ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
            ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids);

            if(!empty($request->search_key)) {
                $preBookingQuery = $preBookingQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("car_registration_no", "like", "%" . $term . "%");
                });

            }

            if (!empty($request->start_date)) {
                $preBookingQuery = $preBookingQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $preBookingQuery = $preBookingQuery->where('created_at', "<=", $request->end_date);
            }
            $pre_bookings = $preBookingQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($pre_bookings, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }

     /**
        *
     * @OA\Get(
     *      path="/v1.0/pre-bookings/single/{garage_id}/{id}",
     *      operationId="getPreBookingById",
     *      tags={"pre_booking_management"},
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
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),

     *      summary="This method is to get pre booking by garae id and id ",
     *      description="This method is to get pre bookings by garage id and id. only supported prebooking will show. the garage must have the sub service selected in the pre booking",
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

    public function getPreBookingById($garage_id,$id,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('job_bids_create')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }

            $garage_sub_service_ids = GarageSubService::
            leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
            ->where([
                "garage_services.garage_id" => $garage_id
            ])
            ->pluck("garage_sub_services.sub_service_id");

            $pre_booking = PreBooking::with("pre_booking_sub_services.sub_service")
            ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
            ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids)
            ->where([
                "pre_bookings.id" => $id
            ])
            ->first();

            if(!$pre_booking) {
return response()->json([
    "message" => "no pre booking found"
],
404);
            }



            return response()->json($pre_booking, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }
}
