<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobBidCreateRequest;
use App\Http\Requests\JobBidUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\PriceUtil;
use App\Models\GarageSubService;
use App\Models\JobBid;
use App\Models\PreBooking;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
                    $query->where("pre_bookings.car_registration_no", "like", "%" . $term . "%");
                });

            }

            if (!empty($request->start_date)) {
                $preBookingQuery = $preBookingQuery->where('pre_bookings.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $preBookingQuery = $preBookingQuery->where('pre_bookings.created_at', "<=", $request->end_date);
            }
            $pre_bookings = $preBookingQuery

            ->orderByDesc("pre_bookings.id")
            ->select(
                "pre_bookings.*"
                )
            ->paginate($perPage);
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
            ->select("pre_bookings.*")
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

 /**
     *
     * @OA\Post(
     *      path="/v1.0/job-bids",
     *      operationId="createJobBid",
     *      tags={"job_bid_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store job bid",
     *      description="This method is to store job bid",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"garage_id","pre_booking_id","price","offer_template","description"},
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     *    @OA\Property(property="pre_booking_id", type="number", format="number",example="1"),
     *    @OA\Property(property="price", type="number", format="number",example="10.99"),
     * *    @OA\Property(property="offer_template", type="string", format="string",example="offer template goes here"),

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

    public function createJobBid(JobBidCreateRequest $request)
    {
        try {

            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('job_bids_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $insertableData = $request->validated();
                if (!$this->garageOwnerCheck($insertableData["garage_id"])) {
                    return response()->json([
                        "message" => "you are not the owner of the garage or the requested garage does not exist."
                    ], 401);
                }

                $garage_sub_service_ids = GarageSubService::
                leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                ->where([
                    "garage_services.garage_id" => $insertableData["garage_id"]
                ])
                ->pluck("garage_sub_services.sub_service_id");

                $pre_booking = PreBooking::with("pre_booking_sub_services.sub_service")
                ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
                ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids)
                ->where([
                    "pre_bookings.id" => $insertableData["pre_booking_id"]
                ])
                ->first();

                if(!$pre_booking) {
    return response()->json([
        "message" => "no pre booking found"
    ],
    404);
                }

                $previous_job_bid = JobBid::where([
                    "pre_booking_id" => $insertableData["pre_booking_id"],
                    "garage_id" => $insertableData["garage_id"],
                ])
                ->first();

                if($previous_job_bid){
                    return response()->json([
                        "message" => "bid already present"
                    ],
                    409);
                }

                $job_bid =  JobBid::create($insertableData);


                return response($job_bid, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }


 /**
     *
     * @OA\Put(
     *      path="/v1.0/job-bids",
     *      operationId="updateJobBid",
     *      tags={"job_bid_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update job bid",
     *      description="This method is to update job bid",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
   *            required={"id","garage_id","pre_booking_id","price","offer_template","description"},
   *    @OA\Property(property="id", type="number", format="number",example="1"),
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     *    @OA\Property(property="pre_booking_id", type="number", format="number",example="1"),
     *    @OA\Property(property="price", type="number", format="number",example="10.99"),
     * *    @OA\Property(property="offer_template", type="string", format="string",example="offer template goes here"),
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

    public function updateJobBid(JobBidUpdateRequest $request)
    {
        try {
            return  DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('fuel_station_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $updatableData = $request->validated();

                if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
                    return response()->json([
                        "message" => "you are not the owner of the garage or the requested garage does not exist."
                    ], 401);
                }

                $garage_sub_service_ids = GarageSubService::
                leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                ->where([
                    "garage_services.garage_id" => $updatableData["garage_id"]
                ])
                ->pluck("garage_sub_services.sub_service_id");

                $pre_booking = PreBooking::with("pre_booking_sub_services.sub_service")
                ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
                ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids)
                ->where([
                    "pre_bookings.id" => $updatableData["pre_booking_id"]
                ])
                ->first();

                if(!$pre_booking) {
    return response()->json([
        "message" => "no pre booking found"
    ],
    404);
      }






                $job_bid  =  tap(JobBid::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        "garage_id",
                        "pre_booking_id",
                        "price",
                        "offer_template",
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                return response($job_bid, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }

  /**
        *
     * @OA\Get(
     *      path="/v1.0/job-bids/{garage_id}/{perPage}",
     *      operationId="getJobBids",
     *      tags={"job_bid_management"},
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
     *      summary="This method is to get job bids",
     *      description="This method is to get job bids",
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

    public function getJobBids($garage_id,$perPage,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('job_bids_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }



            $jobBidQuery = JobBid::with("pre_booking.pre_booking_sub_services")
            ->leftJoin('pre_bookings', 'job_bids.pre_booking_id', '=', 'pre_bookings.id')
           ;

            if(!empty($request->search_key)) {
                $jobBidQuery = $jobBidQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("pre_bookings.car_registration_no", "like", "%" . $term . "%");
                });

            }

            if (!empty($request->start_date)) {
                $jobBidQuery = $jobBidQuery->where('job_bids.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $jobBidQuery = $jobBidQuery->where('job_bids.created_at', "<=", $request->end_date);
            }
            $job_bids = $jobBidQuery->orderByDesc("job_bids.id")
            ->select("job_bids.*")
            ->paginate($perPage);
            return response()->json($job_bids, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }



   /**
        *
     * @OA\Get(
     *      path="/v1.0/job-bids/single/{garage_id}/{id}",
     *      operationId="getJobBidById",
     *      tags={"job_bid_management"},
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

     *      summary="This method is to get job bid by garae id and id ",
     *      description="This method is to get job bid by garage id and id.",
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

    public function getJobBidById($garage_id,$id,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('job_bids_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }



            $job_bid =  JobBid::with("pre_booking.pre_booking_sub_services")
            ->leftJoin('pre_bookings', 'job_bids.pre_booking_id', '=', 'pre_bookings.id')


            ->where([
                "job_bids.id" => $id
            ])
            ->select("job_bids.*")
            ->first();

            if(!$job_bid) {
return response()->json([
    "message" => "no job bid found"
],
404);
            }



            return response()->json($job_bid, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }


     /**
        *
     * @OA\Delete(
     *      path="/v1.0/job-bids/{garage_id}/{id}",
     *      operationId="deleteJobBidById",
     *      tags={"job_bid_management"},
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
     *  example="1"
     *      ),
     *      summary="This method is to  delete job bid by id",
     *      description="This method is to delete job bid by id",
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

    public function deleteJobBidById($garage_id,$id,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('job_bids_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $job_bid = JobBid::where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$job_bid){
                return response()->json([
            "message" => "job bid not found"
                ], 404);
            }
            $job_bid->delete();


            return response()->json($job_bid, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }





}
