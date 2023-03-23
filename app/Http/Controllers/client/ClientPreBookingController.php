<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\PreBookingCreateRequestClient;
use App\Http\Requests\PreBookingUpdateRequestClient;
use App\Http\Utils\ErrorUtil;
use App\Models\AutomobileMake;
use App\Models\AutomobileModel;
use App\Models\PreBooking;
use App\Models\PreBookingSubService;
use App\Models\SubService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClientPreBookingController extends Controller
{
    use ErrorUtil;
       /**
     *
     * @OA\Post(
     *      path="/v1.0/client/pre-bookings",
     *      operationId="createPreBookingClient",
     *      tags={"client.prebooking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store pre  booking",
     *      description="This method is to store pre booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"automobile_make_id","automobile_model_id","car_registration_no","pre_booking_sub_service_ids"},


     *
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),
     * * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     *   * *    @OA\Property(property="additional_information", type="string", format="string",example="r-00011111"),
     *

     *
     *
     * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),


     *  * *    @OA\Property(property="pre_booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *
     *
     *  * @OA\Property(property="country", type="string", format="string",example="country"),
     *  * @OA\Property(property="city", type="string", format="string",example="city"),
     *  * @OA\Property(property="post_code", type="string", format="string",example="postcode"),
     *  * @OA\Property(property="address", type="string", format="string",example="address"),
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

    public function createPreBookingClient(PreBookingCreateRequestClient $request)
    {
        try {

            return DB::transaction(function () use ($request) {
                $insertableData = $request->validated();

                $insertableData["customer_id"] = auth()->user()->id;
                $insertableData["status"] = "pending";





                $automobile_make = AutomobileMake::where([
                   "id" =>  $insertableData["automobile_make_id"]
                ])
                    ->first();
                if (!$automobile_make) {
                    throw new Exception("invalid automobile make id");
                }
                $automobile_model = AutomobileModel::where([
                    "id" => $insertableData["automobile_model_id"],
                    "automobile_make_id" => $automobile_make->id
                ])
                    ->first();
                if (!$automobile_model) {
                    throw new Exception("Invalid automobile model id");
                }






                $pre_booking =  PreBooking::create($insertableData);




                foreach ($insertableData["pre_booking_sub_service_ids"] as $sub_service_id) {
                    $sub_service =  SubService::
                        where([
                            "id" => $sub_service_id,

                        ])

                        ->first();

                    if (!$sub_service) {
                        throw new Exception("invalid service");
                    }






                    $pre_booking->pre_booking_sub_services()->create([
                        "sub_service_id" => $sub_service->id,
                    ]);
                }







                return response($pre_booking, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }




 /**
     *
     * @OA\Put(
     *      path="/v1.0/client/pre-bookings",
     *      operationId="updatePreBookingClient",
     *      tags={"client.prebooking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update pre booking",
     *      description="This method is to update pre booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","automobile_make_id","automobile_model_id","car_registration_no","pre_booking_sub_service_ids"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),

     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),
     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     *  * *    @OA\Property(property="pre_booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *         ),
     *
     *   *
     *  * @OA\Property(property="country", type="string", format="string",example="country"),
     *  * @OA\Property(property="city", type="string", format="string",example="city"),
     *  * @OA\Property(property="post_code", type="string", format="string",example="postcode"),
     *  * @OA\Property(property="address", type="string", format="string",example="address"),
     *
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

    public function updatePreBookingClient(PreBookingUpdateRequestClient $request)
    {
        try {
            return  DB::transaction(function () use ($request) {

                $updatableData = $request->validated();


                $automobile_make = AutomobileMake::where([
                    "id" =>  $updatableData["automobile_make_id"]
                 ])
                     ->first();
                 if (!$automobile_make) {
                     throw new Exception("invalid automobile make id");
                 }
                 $automobile_model = AutomobileModel::where([
                     "id" => $updatableData["automobile_model_id"],
                     "automobile_make_id" => $automobile_make->id
                 ])
                     ->first();
                 if (!$automobile_model) {
                     throw new Exception("Invalid automobile model id");
                 }






                $pre_booking  =  tap(PreBooking::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([

                        "automobile_make_id",
                        "automobile_model_id",
                        "car_registration_no",

        "address",
        "country",
        "city",
        "postcode"

                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$pre_booking) {
                    return response()->json([
                        "message" => "pre booking not found"
                    ], 404);
                }
                PreBookingSubService::where([
                    "pre_booking_id" => $pre_booking->id
                ])->delete();



                foreach ($updatableData["pre_booking_sub_service_ids"] as $sub_service_id) {
                    $sub_service =  SubService::
                        where([
                            "id" => $sub_service_id,

                        ])

                        ->first();

                    if (!$sub_service) {
                        throw new Exception("invalid service");
                    }






                    $pre_booking->pre_booking_sub_services()->create([
                        "sub_service_id" => $sub_service->id,
                    ]);
                }






                return response($pre_booking, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500);
        }
    }




 /**
     *
     * @OA\Get(
     *      path="/v1.0/client/pre-bookings/{perPage}",
     *      operationId="getPreBookingsClient",
     *      tags={"client.prebooking"},
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
     *      summary="This method is to get pre bookings ",
     *      description="This method is to get pre bookings",
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

    public function getPreBookingsClient($perPage, Request $request)
    {
        try {

            $preBookingQuery = PreBooking::with("pre_booking_sub_services.sub_service","job_bids.garage")
                ->where([
                    "customer_id" => $request->user()->id
                ]);

            if (!empty($request->search_key)) {
                $preBookingQuery = $preBookingQuery->where(function ($query) use ($request) {
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
        } catch (Exception $e) {

            return $this->sendError($e, 500);
        }
    }





    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/pre-bookings/single/{id}",
     *      operationId="getPreBookingByIdClient",
     *      tags={"client.prebooking"},
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
     *      summary="This method is to get pre  booking by id ",
     *      description="This method is to get pre booking by id",
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

    public function getPreBookingByIdClient($id, Request $request)
    {
        try {

            $pre_booking = PreBooking::with("pre_booking_sub_services.sub_service","job_bids.garage")
                ->where([
                    "id" => $id,
                    "customer_id" => $request->user()->id
                ])
                ->first();

            if (!$pre_booking) {
                return response()->json([
                    "message" => "booking not found"
                ], 404);
            }


            return response()->json($pre_booking, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500);
        }
    }






    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/client/pre-bookings/{id}",
     *      operationId="deletePreBookingByIdClient",
     *      tags={"client.prebooking"},
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
     *      summary="This method is to delete pre booking by id",
     *      description="This method is to delete pre booking by id",
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

    public function deletePreBookingByIdClient($id, Request $request)
    {

        try {

            PreBooking::where([
                "id" => $id,
                "customer_id" => $request->user()->id
            ])
                ->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500);
        }
    }







}
