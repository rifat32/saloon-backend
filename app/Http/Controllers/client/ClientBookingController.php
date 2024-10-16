<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingCreateRequestClient;
use App\Http\Requests\BookingStatusChangeRequestClient;
use App\Http\Requests\BookingUpdateRequestClient;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\DiscountUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\PriceUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\DynamicMail;
use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\BookingSubService;
use App\Models\Coupon;
use App\Models\ExpertRota;
use App\Models\Garage;
use App\Models\GarageAutomobileMake;
use App\Models\GarageAutomobileModel;
use App\Models\GaragePackage;
use App\Models\GarageSubService;
use App\Models\GarageTime;
use App\Models\Job;
use App\Models\JobBid;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\PreBooking;
use App\Models\SubService;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientBookingController extends Controller
{
    use ErrorUtil, DiscountUtil, PriceUtil, UserActivityUtil, BasicUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/client/bookings",
     *      operationId="createBookingClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store booking",
     *      description="This method is to store booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"garage_id","coupon_code","automobile_make_id","automobile_model_id","car_registration_no","car_registration_year","booking_sub_service_ids","booking_garage_package_ids"},
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),
     * * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     *     * * *    @OA\Property(property="car_registration_year", type="string", format="string",example="2019-06-29"),
     *
     *   * *    @OA\Property(property="additional_information", type="string", format="string",example="r-00011111"),
     *      *       @OA\Property(property="reason", type="string", format="string",example="pending"),
     *
     *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),
     *

     *
     *
     * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),


     *  * *    @OA\Property(property="booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *  *  * *    @OA\Property(property="booking_garage_package_ids", type="string", format="array",example={1,2,3,4}),
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

    public function createBookingClient(BookingCreateRequestClient $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                $insertableData = $request->validated();

                $insertableData["customer_id"] = auth()->user()->id;
                $insertableData["status"] = "pending";
                $insertableData["created_by"] = $request->user()->id;
                $insertableData["created_from"] = "customer_side";


                $garage = Garage::where([
                    "id" => $insertableData["garage_id"]
                ])
                    ->first();

                if (!$garage) {
                    return response()
                        ->json(
                            [
                                "message" => "garage not found."
                            ],
                            404
                        );
                }


                $booking =  Booking::create($insertableData);

                $total_price = 0;
                $total_time = 0;
                foreach ($insertableData["booking_sub_service_ids"] as $index => $sub_service_id) {
                    $sub_service =  SubService::where([
                        "business_id" => $booking->garage_id,
                        "id" => $sub_service_id
                    ])
                        ->first();

                    if (!$sub_service) {
                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_sub_service_ids[" . $index . "]") => ["invalid service"]]
                        ];
                        throw new Exception(json_encode($error), 422);
                    }

                    $price = $this->getPrice($sub_service, $insertableData["expert_id"]);

                    $total_time += $sub_service->service_time_in_minute;

                    $total_price += $price;

                    $booking->booking_sub_services()->create([
                        "sub_service_id" => $sub_service->id,
                        "price" => $price
                    ]);
                }

                $slotValidation =  $this->validateBookingSlots($booking->id, $request["booked_slots"], $request["job_start_date"], $request["expert_id"], $total_time);

                if ($slotValidation['status'] === 'error') {
                    // Return a JSON response with the overlapping slots and a 422 Unprocessable Entity status code
                    return response()->json($slotValidation, 422);
                }


                foreach ($insertableData["booking_garage_package_ids"] as $index => $garage_package_id) {
                    $garage_package =  GaragePackage::where([
                        "garage_id" => $insertableData["garage_id"],
                        "id" => $garage_package_id
                    ])

                        ->first();

                    if (!$garage_package) {

                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_garage_package_ids[" . $index . "]") => ["invalid package"]]
                        ];
                        throw new Exception(json_encode($error), 422);
                    }


                    $total_price += $garage_package->price;

                    $booking->booking_packages()->create([
                        "garage_package_id" => $garage_package->id,
                        "price" => $garage_package->price
                    ]);
                }



                $booking->price = $total_price;
                $booking->save();

                if (!empty($insertableData["coupon_code"])) {
                    $coupon_discount = $this->getCouponDiscount(
                        $insertableData["garage_id"],
                        $insertableData["coupon_code"],
                        $total_price
                    );

                    if ($coupon_discount["success"]) {

                        $booking->coupon_discount_type = $coupon_discount["discount_type"];
                        $booking->coupon_discount_amount = $coupon_discount["discount_amount"];
                        $booking->coupon_code = $insertableData["coupon_code"];

                        $booking->save();

                        Coupon::where([
                            "code" => $booking->coupon_code,
                            "garage_id" => $booking->garage_id
                        ])->update([
                            "customer_redemptions" => DB::raw("customer_redemptions + 1")
                        ]);
                    } else {
                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => ["coupon_code" => [$coupon_discount["message"]]]
                        ];
                        throw new Exception(json_encode($error), 422);
                    }
                }
                $booking->final_price = $booking->price;
                $booking->final_price -= $this->canculate_discounted_price($booking->price, $booking->discount_type, $booking->discount_amount);
                $booking->final_price -= $this->canculate_discounted_price(
                    $booking->price,
                    $booking->coupon_discount_type,

                    $booking->coupon_discount_amount
                );
                $booking->save();



                $notification_template = NotificationTemplate::where([
                    "type" => "booking_created_by_client"
                ])
                    ->first();
                Notification::create([
                    "sender_id" => $request->user()->id,
                    "receiver_id" => $booking->garage->owner_id,
                    "customer_id" => $booking->customer_id,
                    "garage_id" => $booking->garage_id,
                    "booking_id" => $booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);

                // if(env("SEND_EMAIL") == true) {
                //     Mail::to($booking->customer->email)->send(new DynamicMail(
                //     $booking,
                //     "booking_created_by_client"
                // ));
                // }

                return response($booking, 201);
            });
        } catch (Exception $e) {


            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Patch(
     *      path="/v1.0/client/bookings/change-status",
     *      operationId="changeBookingStatusClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to change booking status",
     *      description="This method is to change booking status.
     * if status is accepted. the booking will be converted to a job.  and the status of the job will be pending ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","status"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     * @OA\Property(property="status", type="string", format="string",example="pending"),
     *      *       @OA\Property(property="reason", type="string", format="string",example="pending"),

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

    public function changeBookingStatusClient(BookingStatusChangeRequestClient $request)
    {
        try {
            $this->storeActivity($request, "");
            return  DB::transaction(function () use ($request) {

                $updatableData = $request->validated();

                $booking = Booking::where([
                    "id" => $updatableData["id"],
                    "customer_id" =>  auth()->user()->id
                ])
                    ->first();
                if (!$booking) {
                    return response()->json([
                        "message" => "booking not found"
                    ], 404);
                }
                if ($booking->status === "converted_to_job") {
                    // Return an error response indicating that the status cannot be updated
                    return response()->json(["message" => "Status cannot be updated because it is completed"], 422);
                }

                if ( $booking->status == "rejected_by_garage_owner" ||  $booking->status == "rejected_by_client") {
                    // Return an error response indicating that the status cannot be updated
                    return response()->json(["message" => "Status cannot be updated because it is in cancelled status"], 422);
                }

                $jobStartDate = Carbon::parse($booking->job_start_date);

                if (Carbon::now()->gte($jobStartDate) || Carbon::now()->diffInHours($jobStartDate, false) < 24) {
                    return response()->json(['error' => 'Booking status cannot be changed within 24 hours of the job start time or if the time has already passed'], 409);
                }





                    $booking->status = $updatableData["status"];
                    $booking->status = $updatableData["reason"] ?? NULL;

                    $booking->save();


                    $notification_template = NotificationTemplate::where([
                        "type" => "booking_status_changed_by_garage_owner"
                    ])
                        ->first();
                    Notification::create([
                        "sender_id" => $request->user()->id,
                        "receiver_id" => $booking->garage->owner_id,
                        "customer_id" => $booking->customer_id,
                        "garage_id" => $booking->garage_id,
                        "booking_id" => $booking->id,
                        "notification_template_id" => $notification_template->id,
                        "status" => "unread",
                    ]);
                    // if(env("SEND_EMAIL") == true) {
                    //     Mail::to($booking->customer->email)->send(new DynamicMail(
                    //     $booking,
                    //     "booking_rejected_by_client"
                    // ));
                    // }




                return response([
                    "ok" => true
                ], 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/client/bookings",
     *      operationId="updateBookingClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update booking",
     *      description="This method is to update booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","coupon_code","automobile_make_id","automobile_model_id","car_registration_no","car_registration_year","booking_sub_service_ids","booking_garage_package_ids"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     * *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),
     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     * *     * * *    @OA\Property(property="car_registration_year", type="string", format="string",example="2019-06-29"),
     *
     *    *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),
     *      *       @OA\Property(property="reason", type="string", format="string",example="pending"),
     *
     *
     *
     *  * *    @OA\Property(property="booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *   *  * *    @OA\Property(property="booking_garage_package_ids", type="string", format="array",example={1,2,3,4}),
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

    public function updateBookingClient(BookingUpdateRequestClient $request)
    {
        try {
            $this->storeActivity($request, "");
            return  DB::transaction(function () use ($request) {

                $updatableData = $request->validated();

                $garage = Garage::where([
                    "id" => $updatableData["garage_id"]
                ])
                    ->first();

                if (!$garage) {
                    return response()
                        ->json(
                            [
                                "message" => "garage not found."
                            ],
                            404
                        );
                }






                $booking  =  tap(Booking::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        "garage_id",
                        "additional_information",
                        "coupon_code",
                        "expert_id",
                        "booked_slots",
                        "reason",

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$booking) {
                    return response()->json([
                        "message" => "booking not found"
                    ], 404);
                }
                BookingSubService::where([
                    "booking_id" => $booking->id
                ])->delete();

                $total_price = 0;
                $total_time = 0;
                foreach ($request["booking_sub_service_ids"] as $index => $sub_service_id) {
                    $sub_service =  SubService::where([
                        "business_id" => auth()->user()->business_id,
                        "id" => $sub_service_id
                    ])
                        ->first();

                    if (!$sub_service) {
                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_sub_service_ids[" . $index . "]") => ["invalid service"]]
                        ];
                        throw new Exception(json_encode($error), 422);
                    }

                    $price = $this->getPrice($sub_service, $request["expert_id"]);

                    $total_time += $sub_service->service_time_in_minute;


                    $total_price += $price;

                    $booking->booking_sub_services()->create([
                        "sub_service_id" => $sub_service->id,
                        "price" => $price
                    ]);
                }

                $slotValidation =  $this->validateBookingSlots($booking->id, $request["booked_slots"], $request["job_start_date"], $request["expert_id"], $total_time);

                if ($slotValidation['status'] === 'error') {
                    // Return a JSON response with the overlapping slots and a 422 Unprocessable Entity status code
                    return response()->json($slotValidation, 422);
                }

                foreach ($updatableData["booking_garage_package_ids"] as $index => $garage_package_id) {
                    $garage_package =  GaragePackage::where([
                        "garage_id" => $booking->garage_id,
                        "id" => $garage_package_id
                    ])

                        ->first();

                    if (!$garage_package) {
                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_garage_package_ids[" . $index . "]") => ["invalid package"]]
                        ];
                        throw new Exception(json_encode($error), 422);
                    }


                    $total_price += $garage_package->price;

                    $booking->booking_packages()->create([
                        "garage_package_id" => $garage_package->id,
                        "price" => $garage_package->price
                    ]);
                }

                // $booking->price = (!empty($updatableData["price"]?$updatableData["price"]:$total_price));
                $booking->price = $total_price;






                // if(!empty($updatableData["coupon_code"])){
                //     $coupon_discount = $this->getCouponDiscount(
                //         $updatableData["garage_id"],
                //         $updatableData["coupon_code"],
                //         $booking->price
                //     );

                //     if($coupon_discount) {

                //         $booking->coupon_discount_type = $coupon_discount["discount_type"];
                //         $booking->coupon_discount_amount = $coupon_discount["discount_amount"];


                //     }
                // }


                $booking->final_price = $booking->price;
                $booking->final_price -= $this->canculate_discounted_price($booking->price, $booking->discount_type, $booking->discount_amount);
                $booking->final_price -= $this->canculate_discounted_price($booking->price, $booking->coupon_discount_type, $booking->coupon_discount_amount);
                $booking->save();


                $notification_template = NotificationTemplate::where([
                    "type" => "booking_updated_by_client"
                ])
                    ->first();
                Notification::create([
                    "sender_id" => $request->user()->id,
                    "receiver_id" => $booking->garage->owner_id,
                    "customer_id" => $booking->customer_id,
                    "garage_id" => $booking->garage_id,
                    "booking_id" => $booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);
                // if(env("SEND_EMAIL") == true) {
                //     Mail::to($booking->customer->email)->send(new DynamicMail(
                //     $booking,
                //     "booking_updated_by_client"
                // ));}


                return response($booking, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/bookings/{perPage}",
     *      operationId="getBookingsClient",
     *      tags={"client.booking"},
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
     *      *      * *  @OA\Parameter(
     * name="status",
     * in="query",
     * description="status",
     * required=true,
     * example="pending"
     * ),
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

    public function getBookingsClient($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");
            $bookingQuery = Booking::with(
                "sub_services.service",
                "booking_packages.garage_package",
                "customer",
                "garage",
                "expert"
            )
                ->where([
                    "customer_id" => $request->user()->id
                ])
                ->when(request()->input("expert_id"), function($query) {
                    $query ->where([
                       "expert_id" => request()->input("expert_id")
                    ]);
               });

            // Apply the existing status filter if provided in the request
            if (!empty($request->status)) {
                $statusArray = explode(',', request()->status);
                // If status is provided, include the condition in the query
                $bookingQuery->whereIn("status", $statusArray);
            }

            if (!empty($request->search_key)) {
                $bookingQuery = $bookingQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("car_registration_no", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $bookingQuery = $bookingQuery->where('job_start_date', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $bookingQuery = $bookingQuery->where('job_start_date', '<=', $request->end_date);
            }

            // Additional date filters using date_filter
            if ($request->date_filter === 'today') {
                $bookingQuery = $bookingQuery->whereDate('job_start_date', Carbon::today());
            } elseif ($request->date_filter === 'this_week') {
                $bookingQuery = $bookingQuery->whereBetween('job_start_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($request->date_filter === 'previous_week') {
                $bookingQuery = $bookingQuery->whereBetween('job_start_date', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()]);
            } elseif ($request->date_filter === 'next_week') {
                $bookingQuery = $bookingQuery->whereBetween('job_start_date', [Carbon::now()->addWeek()->startOfWeek(), Carbon::now()->addWeek()->endOfWeek()]);
            } elseif ($request->date_filter === 'this_month') {
                $bookingQuery = $bookingQuery->whereMonth('job_start_date', Carbon::now()->month)
                    ->whereYear('job_start_date', Carbon::now()->year);
            } elseif ($request->date_filter === 'previous_month') {
                $bookingQuery = $bookingQuery->whereMonth('job_start_date', Carbon::now()->subMonth()->month)
                    ->whereYear('job_start_date', Carbon::now()->subMonth()->year);
            } elseif ($request->date_filter === 'next_month') {
                $bookingQuery = $bookingQuery->whereMonth('job_start_date', Carbon::now()->addMonth()->month)
                    ->whereYear('job_start_date', Carbon::now()->addMonth()->year);
            }




            $bookings = $bookingQuery->orderByDesc("job_start_date")->paginate($perPage);


            return response()->json($bookings, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


     /**
     *
     * @OA\Get(
     *      path="/v1.0/client/available-experts",
     *      operationId="getAvailableExpertsClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *
     *      * *  @OA\Parameter(
     * name="business_id",
     * in="query",
     * description="business_id",
     * required=true,
     * example=""
     * ),
     *      * *  @OA\Parameter(
     * name="date",
     * in="query",
     * description="date",
     * required=true,
     * example=""
     * ),

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

     public function getAvailableExpertsClient( Request $request)
     {
         try {
             $this->storeActivity($request, "");

             if (!request()->filled("date")) {
                 return response()->json([
                     "message" => "Date field is required"
                 ], 401);
             }
             if (!request()->filled("business_id")) {
                return response()->json([
                    "message" => "business_id field is required"
                ], 401);
            }

            if (!request()->filled("slots")) {
                return response()->json([
                    "message" => "slots field is required"
                ], 401);
            }


             $experts = User::with("translation")
             ->whereHas('roles', function($query) {
                 $query->where('roles.name', 'business_experts');
             })
             ->when(request()->filled("business_id"), function($query){
                 $query->where("business_id", request()->input("business_id"));
             })
             ->get();

             $availableExperts = collect();

             foreach($experts as $expert) {
                 // Get all bookings for the provided date except the rejected ones
        $expert_bookings = Booking::whereDate("job_start_date", request()->input("date"))
            ->whereNotIn("status", ["rejected_by_client", "rejected_by_garage_owner"])
            ->where([
                "business_id" => request()->input("business_id")
            ])
            ->get();


        // Get all the booked slots as a flat array
        $allBusySlots = $expert_bookings->pluck('booked_slots')->flatten()->toArray();


        $expertRota = ExpertRota::where([
            "expert_id" =>  $expert->id
        ])
        ->whereDate("date",request()->input("date"))
        ->first();

        if(!empty($expertRota)) {
          $expertRota->busy_slots;
        }

    // If expertRota exists, merge its busy_slots with the booked slots
    if (!empty($expertRota) && !empty($expertRota->busy_slots)) {
        $allBusySlots = array_merge($allBusySlots, $expertRota->busy_slots);
    }

    $slots = explode(',', request()->input("slots"));
    // Find overlapping slots between the input slots and the combined allBusySlots
    $overlappingSlots = array_intersect($slots, $allBusySlots);

        // If there are overlaps, return them or throw an error
        if (!empty($overlappingSlots)) {
            return [
                'status' => 'error',
                'message' => 'Some slots are already booked.',
                'overlapping_slots' => $overlappingSlots
            ];
        } else {
           $availableExperts->push($expert);
        }

             }

             return response()->json($availableExperts->toArray(), 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/blocked-slots/{expert_id}",
     *      operationId="getBlockedSlotsClient",
     *      tags={"client.booking"},
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
     *      *      * *  @OA\Parameter(
     * name="status",
     * in="query",
     * description="status",
     * required=true,
     * example="pending"
     * ),
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

    public function getBlockedSlotsClient($expert_id, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            if (!request()->filled("date")) {
                return response()->json([
                    "message" => "Date field is required"
                ], 401);
            }


            // Get all bookings for the provided date except the rejected ones
            $bookings = Booking::with([
                    "customer" => function ($query) {
                        $query->select("users.id", "users.first_Name", "users.last_Name");
                    }
                ])
                ->whereDate("job_start_date", request()->input("date"))
                ->whereNotIn("status", ["rejected_by_client", "rejected_by_garage_owner"])
                ->where([
                    "expert_id" => $expert_id
                ])
                ->select("id", "booked_slots", "customer_id")

                ->get();

            // Get all the booked slots as a flat array

            $data["bookings"] = $bookings;
            $data["booking_slots"] = $bookings->pluck('booked_slots')->flatten()->toArray();

  // Get all bookings for the provided date except the rejected ones
  $check_in_bookings = Booking::
whereDate("job_start_date", request()->input("date"))
->whereIn("status", ["check_in"])
->where([
    "expert_id" => $expert_id
])
->get();

$data["check_in_slots"]  = $check_in_bookings->pluck('booked_slots')->flatten()->toArray();





            $expertRota = ExpertRota::where([
                "expert_id" =>  $expert_id
            ])
                ->whereDate("date", request()->input("date"))
                ->first();
            if (!empty($expertRota)) {
                $expertRota->busy_slots;
            }
            $data["busy_slots"] = [];
            // If expertRota exists, merge its busy_slots with the booked slots
            if (!empty($expertRota)) {
                $data["busy_slots"] = $expertRota->busy_slots;
            }
            // else {
            //     return response()->json([
            //             "message" => "No slots are available"
            //     ], 400);
            // }

            return response()->json($data, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }





    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/bookings/single/{id}",
     *      operationId="getBookingByIdClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to get  booking by id ",
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

    public function getBookingByIdClient($id, Request $request)
    {
        try {
            $this->storeActivity($request, "");
            $booking = Booking::with("booking_sub_services.sub_service")
                ->where([
                    "id" => $id,
                    "customer_id" => $request->user()->id
                ])
                ->first();

            if (!$booking) {
                return response()->json([
                    "message" => "booking not found"
                ], 404);
            }


            return response()->json($booking, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }






    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/client/bookings/{id}",
     *      operationId="deleteBookingByIdClient",
     *      tags={"client.booking"},
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
     *      summary="This method is to delete booking by id",
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

    public function deleteBookingByIdClient($id, Request $request)
    {

        try {
            $this->storeActivity($request, "");
            $booking =  Booking::where([
                "id" => $id,
                "customer_id" => $request->user()->id
            ])->first();
            if (!$booking) {
                return response()->json(
                    [
                        "message" => "no booking found"
                    ],
                    404
                );
            }




            if ($booking->status != "pending") {
                // Return an error response indicating that the status cannot be updated
                return response()->json(["message" => "only pending booking can be deleted"], 422);
            }

            $jobStartDate = Carbon::parse($booking->job_start_date);

            if (Carbon::now()->gte($jobStartDate) || Carbon::now()->diffInHours($jobStartDate, false) < 24) {
                return response()->json(['error' => 'Booking cannot be deleted within 24 hours of the job start time or if the time has already passed'], 409);
            }




            $booking->delete();

            $notification_template = NotificationTemplate::where([
                "type" => "booking_deleted_by_client"
            ])
                ->first();
            Notification::create([
                "sender_id" => $request->user()->id,
                "receiver_id" => $booking->garage->owner_id,
                "customer_id" => $booking->customer_id,
                "garage_id" => $booking->garage_id,
                "booking_id" => $booking->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
            //     if(env("SEND_EMAIL") == true) {
            //         Mail::to($booking->customer->email)->send(new DynamicMail(
            //         $booking,
            //         "booking_deleted_by_client"
            //     ));
            // }

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
