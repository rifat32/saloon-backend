<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingConfirmRequest;

use App\Http\Requests\BookingUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Models\Booking;
use App\Models\BookingSubService;
use App\Models\GarageSubService;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use ErrorUtil,GarageUtil;


       /**
        *
     * @OA\Put(
     *      path="/v1.0/bookings",
     *      operationId="updateBooking",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update booking",
     *      description="This method is to update booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","automobile_make_id","automobile_model_id","car_registration_no","booking_sub_service_ids","job_start_time","job_end_time"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),

     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),

     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     *  * *    @OA\Property(property="booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *  *  * *
     * @OA\Property(property="job_start_time", type="string", format="string",example="2019-06-29"),
     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="2019-06-29"),
     *
     *
     *         ),

     *
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

    public function updateBooking(BookingUpdateRequest $request)
    {
        try{
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('booking_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
    $updatableData = $request->validated();
    if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
        return response()->json([
            "message" => "you are not the owner of the garage or the requested garage does not exist."
        ], 401);
    }


        $booking  =  tap(Booking::where([
            "id" => $updatableData["id"],
            "garage_id" =>  $updatableData["garage_id"]
            ]))->update(collect($updatableData)->only([
            "automobile_make_id",
            "automobile_model_id",

            "car_registration_no",
            "status",
             "job_start_time",
            "job_end_time",
        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            BookingSubService::where([
               "booking_id" => $booking->id
            ])->delete();


            foreach($updatableData["booking_sub_service_ids"] as $sub_service_id) {
                $garage_sub_service =  GarageSubService::leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                    ->where([
                        "garage_services.garage_id" => $booking->garage_id,
                        "garage_sub_services.sub_service_id" => $sub_service_id
                    ])
                    ->select(
                        "garage_sub_services.id",
                        "garage_sub_services.sub_service_id",
                        "garage_sub_services.garage_service_id"
                    )
                    ->first();

                    if(!$garage_sub_service ){
                 throw new Exception("invalid service");
                    }
                    BookingSubService::create([
                        "sub_service_id" => $garage_sub_service->id,
                        "booking_id" => $booking->id
                    ]);

                }






    return response($booking, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }


    /**
        *
     * @OA\Put(
     *      path="/v1.0/bookings/confirm",
     *      operationId="confirmBooking",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to confirm booking",
     *      description="This method is to confirm booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","job_start_time","job_end_time"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
 * @OA\Property(property="garage_id", type="number", format="number",example="1"),
       * @OA\Property(property="job_start_time", type="string", format="string",example="2019-06-29"),
     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="2019-06-29"),



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

    public function confirmBooking(BookingConfirmRequest $request)
    {
        try{
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('booking_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
   $updatableData = $request->validated();
   if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
    return response()->json([
        "message" => "you are not the owner of the garage or the requested garage does not exist."
    ], 401);
}

    $updatableData["status"] = "confirmed";
        $booking  =  tap(Booking::where([
            "id" => $updatableData["id"],
            "garage_id" =>  $updatableData["garage_id"]
        ]))->update(collect($updatableData)->only([
            "job_start_time",
            "job_end_time",
            "status",
        ])->toArray()
        )
            // ->with("somthing")

            ->first();

    return response($booking, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }




 /**
        *
     * @OA\Get(
     *      path="/v1.0/bookings/{garage_id}/{perPage}",
     *      operationId="getBookings",
     *      tags={"booking_management"},
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
     *      summary="This method is to get  bookings ",
     *      description="This method is to get bookings",
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

    public function getBookings($garage_id,$perPage,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('booking_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $bookingQuery = Booking::with("booking_sub_services")
            ->where([
                "garage_id" => $garage_id
            ]);

            if(!empty($request->search_key)) {
                $bookingQuery = $bookingQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("car_registration_no", "like", "%" . $term . "%");
                });

            }

            if(!empty($request->start_date) && !empty($request->end_date)) {
                $bookingQuery = $bookingQuery->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);

            }
            $bookings = $bookingQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($bookings, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }


     /**
        *
     * @OA\Get(
     *      path="/v1.0/bookings/single/{garage_id}/{id}",
     *      operationId="getBookingById",
     *      tags={"booking_management"},
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
     *      summary="This method is to  get booking by id",
     *      description="This method is to get booking by id",
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

    public function getBookingById($garage_id,$id,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('booking_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $booking = Booking::with("booking_sub_services")
            ->where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }


            return response()->json($booking, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }


 /**
        *
     * @OA\Delete(
     *      path="/v1.0/bookings/{garage_id}/{id}",
     *      operationId="deleteBookingById",
     *      tags={"booking_management"},
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
     *      summary="This method is to  delete booking by id",
     *      description="This method is to delete booking by id",
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

    public function deleteBookingById($garage_id,$id,Request $request) {
        try{
            if(!$request->user()->hasPermissionTo('booking_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $booking = Booking::where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }
            $booking->delete();


            return response()->json($booking, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
        }
    }

















}
