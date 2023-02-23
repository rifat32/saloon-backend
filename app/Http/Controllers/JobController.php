<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingToJobRequest;
use App\Models\Booking;
use App\Models\BookingSubService;
use App\Models\Job;
use App\Models\JobSubService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{

      /**
        *
     * @OA\Patch(
     *      path="/v1.0/jobs/booking-to-job",
     *      operationId="bookingToJob",
     *      tags={"job_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to convert booking to job",
     *      description="This method is to convert booking to job",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
   * *   required={"booking_id","garage_id","discount_type","discount","price"},
   * *    @OA\Property(property="booking_id", type="number", format="number",example="1"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
 *  * *    @OA\Property(property="discount_type", type="string", format="string",example="percentage"),
 * *  * *    @OA\Property(property="discount", type="number", format="number",example="percentage"),
 *  * *  * *    @OA\Property(property="price", type="number", format="number",example="percentage"),
     *
     *
     *         ),
     *  * *

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

    public function bookingToJob(BookingToJobRequest $request)
    {
        try{
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('job_create')){
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


        $booking  = Booking::where([
            "id" => $updatableData["booking_id"],
            "garage_id" =>  $updatableData["garage_id"]
            ])
            ->first();


                if(!$booking){
                    return response()->json([
                "message" => "booking not found"
                    ], 404);
                }


                $job = Job::create([
                    "garage_id" => $booking->garage_id,
                    "customer_id" => $booking->customer_id,
                    "automobile_make_id"=> $booking->automobile_make_id,
                    "automobile_model_id"=> $booking->automobile_model_id,
                    "car_registration_no"=> $booking->car_registration_no,
                    "additional_information" => $booking->additional_information,
                    "job_start_time"=> $booking->job_start_time,
                    "job_end_time"=> $booking->job_end_time,


                    "discount_type" => $updatableData["discount_type"],
                    "discount"=> $updatableData["discount"],
                    "price"=>$updatableData["price"],


                    "status" => "complete",
                    "payment_status" => "due",
                ]);

                foreach(

                BookingSubService::where([
                    "booking_id" => $booking->id
                ])->get()
                as
                $booking_sub_service
                ) {

                 JobSubService::create([
                    "job_id" => $job->id,
                    "sub_service_id" => $booking_sub_service->sub_service_id
                 ]);

                }

                $booking->delete();


    return response($booking, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
        }
    }


}
