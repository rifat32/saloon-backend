<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Models\Affiliation;
use App\Models\Booking;
use App\Models\Garage;
use App\Models\GarageAffiliation;
use App\Models\Job;
use App\Models\PreBooking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardManagementController extends Controller
{
    use ErrorUtil, GarageUtil;

    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/jobs-in-area/{garage_id}",
     *      operationId="getGarageOwnerDashboardDataJobList",
     *      tags={"dashboard_management.garage_owner"},
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
     *      *      * *  @OA\Parameter(
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
     *      summary="This should return list of jobs posted by drivers within same city and which are still not finalised and this garage owner have not applied yet.",
     *      description="This should return list of jobs posted by drivers within same city and which are still not finalised and this garage owner have not applied yet.",
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

    public function getGarageOwnerDashboardDataJobList($garage_id, Request $request)
    {
        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }

        $prebookingQuery = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereNotIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending");


        if (!empty($request->start_date)) {
            $prebookingQuery = $prebookingQuery->where('pre_bookings.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $prebookingQuery = $prebookingQuery->where('pre_bookings.created_at', "<=", $request->end_date);
        }
        $data = $prebookingQuery->groupBy("pre_bookings.id")
            ->select(
                "pre_bookings.*",
                DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id) AS job_bids_count'),

                DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids
    WHERE
    job_bids.pre_booking_id = pre_bookings.id
    AND
    job_bids.garage_id = ' . $garage->id . '

    ) AS garage_applied')

            )
            ->havingRaw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id)  < 4')

            ->get();
        return response()->json($data, 200);
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/jobs-application/{garage_id}",
     *      operationId="getGarageOwnerDashboardDataJobApplications",
     *      tags={"dashboard_management.garage_owner"},
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
     *      summary="Total number of Jobs in the area and out of which total number of jobs this garage owner have applied",
     *      description="Total number of Jobs in the area and out of which total number of jobs this garage owner have applied",
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

    public function getGarageOwnerDashboardDataJobApplications($garage_id, Request $request)
    {
        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }

        $data["total_jobs"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])
            //  ->whereNotIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->groupBy("pre_bookings.id")


            ->count();

        $data["weekly_jobs"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])
            //  ->whereNotIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy("pre_bookings.id")
            ->count();
        $data["monthly_jobs"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])
            //  ->whereNotIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy("pre_bookings.id")
            ->count();




        $data["applied_total_jobs"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->groupBy("pre_bookings.id")

            ->count();
        $data["applied_weekly_jobs"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy("pre_bookings.id")

            ->count();
        $data["applied_monthly_jobs"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy("pre_bookings.id")

            ->count();

        return response()->json($data, 200);
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/winned-jobs-application/{garage_id}",
     *      operationId="getGarageOwnerDashboardDataWinnedJobApplications",
     *      tags={"dashboard_management.garage_owner"},
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
     *      summary="Total Job Won( Total job User have selcted this garage )",
     *      description="Total Job Won( Total job User have selcted this garage )",
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

    public function getGarageOwnerDashboardDataWinnedJobApplications($garage_id, Request $request)
    {
        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }

        $data["total"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])

            ->where('pre_bookings.status', "booked")
            ->groupBy("pre_bookings.id")
            ->count();

        $data["weekly"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])
            ->where('pre_bookings.status', "booked")
            ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->groupBy("pre_bookings.id")
            ->count();

        $data["monthly"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])

            ->where('pre_bookings.status', "booked")
            ->whereBetween('pre_bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->groupBy("pre_bookings.id")
            ->count();


        return response()->json($data, 200);
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/completed-bookings/{garage_id}",
     *      operationId="getGarageOwnerDashboardDataCompletedBookings",
     *      tags={"dashboard_management.garage_owner"},
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
     *      summary="Total completed Bookings Total Bookings completed by this garage owner",
     *      description="Total completed Bookings Total Bookings completed by this garage owner",
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

    public function getGarageOwnerDashboardDataCompletedBookings($garage_id, Request $request)
    {
        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }

        $data["total"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->count();
        $data["weekly"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->whereBetween('bookings.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        $data["monthly"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->whereBetween('bookings.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();




        return response()->json($data, 200);
    }




    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/upcoming-jobs/{garage_id}/{duration}",
     *      operationId="getGarageOwnerDashboardDataUpcomingJobs",
     *      tags={"dashboard_management.garage_owner"},
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
     *         name="duration",
     *         in="path",
     *         description="duration",
     *         required=true,
     *  example="7"
     *      ),
     *      summary="Total completed Bookings Total Bookings completed by this garage owner",
     *      description="Total completed Bookings Total Bookings completed by this garage owner",
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

    public function getGarageOwnerDashboardDataUpcomingJobs($garage_id, $duration, Request $request)
    {
        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }
        $startDate = now();
        $endDate = $startDate->copy()->addDays($duration);


        $data = Job::where([
            "jobs.status" => "pending",
            "jobs.garage_id" => $garage->id

        ])
            ->whereBetween('jobs.job_start_date', [$startDate, $endDate])




            ->count();



        return response()->json($data, 200);
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/expiring-affiliations/{garage_id}/{duration}",
     *      operationId="getGarageOwnerDashboardDataExpiringAffiliations",
     *      tags={"dashboard_management.garage_owner"},
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
     *         name="duration",
     *         in="path",
     *         description="duration",
     *         required=true,
     *  example="7"
     *      ),
     *      summary="Total completed Bookings Total Bookings completed by this garage owner",
     *      description="Total completed Bookings Total Bookings completed by this garage owner",
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

    public function getGarageOwnerDashboardDataExpiringAffiliations($garage_id, $duration, Request $request)
    {
        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }
        $startDate = now();
        $endDate = $startDate->copy()->addDays($duration);


        $data = GarageAffiliation::with("affiliation")
            ->where('garage_affiliations.end_date', "<",  $endDate)
            ->count();



        return response()->json($data, 200);
    }





    /**
     *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/{garage_id}",
     *      operationId="getGarageOwnerDashboardData",
     *      tags={"dashboard_management.garage_owner"},
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

     *      summary="get all dashboard data combined",
     *      description="get all dashboard data combined",
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

    public function getGarageOwnerDashboardData($garage_id, Request $request)
    {

        $garage = Garage::where([
            "id" => $garage_id,
            "owner_id" => $request->user()->id
        ])
            ->first();
        if (!$garage) {
            return response()->json([
                "message" => "you are not the owner of the garage or the request garage does not exits"
            ], 404);
        }


        // affiliation expiry
        $data["affiliation_expirings"] = $this->affiliation_expirings($garage);

        //    end affiliation expiry
        //   upcoming_jobs
        $data["upcoming_jobs"] = $this->upcoming_jobs($garage);

        //  end  upcoming_jobs

        // completed bookings
        $data["completed_bookings"] = $this->completed_bookings($garage);
        // end completed bookings

        // winned jobs
        $data["winned_jobs"] = $this->winned_jobs($garage);
        // end winned jobs

        //   jobs
        $data["pre_bookings"] = $this->pre_bookings($garage);
        // end jobs


        // applied jobs
        $data["applied_jobs"] = $this->applied_jobs($garage);
        // end applied jobs


        return response()->json($data, 200);
    }


    public function applied_jobs($garage)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);

        $data["total_count"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->groupBy("pre_bookings.id")
            ->count();





        $data["this_week_data"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")

            ->whereBetween('pre_bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->groupBy("pre_bookings.id")
            ->select("job_bids.id","job_bids.created_at","job_bids.updated_at")
            ->get();

        $data["previous_week_data"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")

            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->groupBy("pre_bookings.id")
            ->select("job_bids.id","job_bids.created_at","job_bids.updated_at")
            ->get();



        $data["this_month_data"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->groupBy("pre_bookings.id")
            ->select("job_bids.id","job_bids.created_at","job_bids.updated_at")
            ->get();

        $data["previous_month_data"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
            ->where([
                "pre_bookings.city" => $garage->city
            ])
            ->whereIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->groupBy("pre_bookings.id")
            ->select("job_bids.id","job_bids.created_at","job_bids.updated_at")
            ->get();

            $data["this_week_data_count"] = $data["this_week_data"]->count();
            $data["previous_week_data_count"] = $data["previous_week_data"]->count();
            $data["this_month_data_count"] = $data["this_month_data"]->count();
            $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        return $data;
    }
    public function pre_bookings($garage)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);
        $data["total_count"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])
            //  ->whereNotIn('job_bids.garage_id', [$garage->id])
            ->where('pre_bookings.status', "pending")
            ->count();



        $data["this_week_data"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])

            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->select("pre_bookings.id","pre_bookings.created_at","pre_bookings.updated_at")
            ->get();

        $data["previous_week_data"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])

            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->select("pre_bookings.id","pre_bookings.created_at","pre_bookings.updated_at")
            ->get();



        $data["this_month_data"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])

            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->select("pre_bookings.id","pre_bookings.created_at","pre_bookings.updated_at")
            ->get();

        $data["previous_month_data"] = PreBooking::where([
            "pre_bookings.city" => $garage->city
        ])

            ->where('pre_bookings.status', "pending")
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->select("pre_bookings.id","pre_bookings.created_at","pre_bookings.updated_at")
            ->get();


            $data["this_week_data_count"] = $data["this_week_data"]->count();
            $data["previous_week_data_count"] = $data["previous_week_data"]->count();
            $data["this_month_data_count"] = $data["this_month_data"]->count();
            $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        return $data;
    }

    public function winned_jobs($garage)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);
        $data["total_data_count"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])

            ->where('pre_bookings.status', "booked")
            ->groupBy("pre_bookings.id")
            ->count();







        $data["this_week_data"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])
            ->where('pre_bookings.status', "booked")
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->groupBy("pre_bookings.id")
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();
        $data["previous_week_data"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])
            ->where('pre_bookings.status', "booked")
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->groupBy("pre_bookings.id")
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();



        $data["this_month_data"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])
            ->where('pre_bookings.status', "booked")
            ->whereBetween('pre_bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->groupBy("pre_bookings.id")
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();

        $data["previous_month_data"] = PreBooking::leftJoin('bookings', 'pre_bookings.id', '=', 'bookings.pre_booking_id')
            ->where([
                "bookings.garage_id" => $garage->id
            ])
            ->where('pre_bookings.status', "booked")
            ->whereBetween('pre_bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->groupBy("pre_bookings.id")
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();

            $data["this_week_data_count"] = $data["this_week_data"]->count();
            $data["previous_week_data_count"] = $data["previous_week_data"]->count();
            $data["this_month_data_count"] = $data["this_month_data"]->count();
            $data["previous_month_data_count"] = $data["previous_month_data"]->count();

        return $data;
    }


    public function completed_bookings($garage)
    {
        $startDateOfThisMonth = Carbon::now()->startOfMonth();
        $endDateOfThisMonth = Carbon::now()->endOfMonth();
        $startDateOfPreviousMonth = Carbon::now()->startOfMonth()->subMonth(1);
        $endDateOfPreviousMonth = Carbon::now()->endOfMonth()->subMonth(1);

        $startDateOfThisWeek = Carbon::now()->startOfWeek();
        $endDateOfThisWeek = Carbon::now()->endOfWeek();
        $startDateOfPreviousWeek = Carbon::now()->startOfWeek()->subWeek(1);
        $endDateOfPreviousWeek = Carbon::now()->endOfWeek()->subWeek(1);
        $data["total_data_count"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->count();






        $data["this_week_data"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfThisWeek, $endDateOfThisWeek])
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();
        $data["previous_week_data"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfPreviousWeek, $endDateOfPreviousWeek])
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();



        $data["this_month_data"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfThisMonth, $endDateOfThisMonth])
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();
        $data["previous_month_data"] = Booking::where([
            "bookings.status" => "converted_to_job",
            "bookings.garage_id" => $garage->id

        ])
            ->whereBetween('bookings.created_at', [$startDateOfPreviousMonth, $endDateOfPreviousMonth])
            ->select("bookings.id","bookings.created_at","bookings.updated_at")
            ->get();

            $data["this_week_data_count"] = $data["this_week_data"]->count();
            $data["previous_week_data_count"] = $data["previous_week_data"]->count();
            $data["this_month_data_count"] = $data["this_month_data"]->count();
            $data["previous_month_data_count"] = $data["previous_month_data"]->count();
        return $data;
    }

    public function upcoming_jobs($garage)
    {
        $startDate = now();
        $weeklyEndDate = $startDate->copy()->addDays(7);
        $secondWeeklyStartDate = $startDate->copy()->addDays(8);
        $secondWeeklyEndDate = $startDate->copy()->addDays(14);
        $monthlyEndDate = $startDate->copy()->addDays(30);
        $secondMonthlyStartDate = $startDate->copy()->addDays(31);
        $secondMonthlyStartDate = $startDate->copy()->addDays(60);
        $data["total_data_count"] = Job::where([
            "jobs.status" => "pending",
            "jobs.garage_id" => $garage->id

        ])
            ->count();


        $data["upcoming_first_week_data"] = Job::where([
            "jobs.status" => "pending",
            "jobs.garage_id" => $garage->id

        ])->whereBetween('jobs.job_start_date', [$startDate, $weeklyEndDate])
        ->select("jobs.id","jobs.created_at","jobs.updated_at")
            ->get();
        $data["upcoming_second_week_data"] = Job::where([
            "jobs.status" => "pending",
            "jobs.garage_id" => $garage->id

        ])->whereBetween('jobs.job_start_date', [$secondWeeklyStartDate, $secondWeeklyEndDate])
        ->select("jobs.id","jobs.created_at","jobs.updated_at")
            ->get();

        $data["upcoming_first_month_data"] = Job::where([
            "jobs.status" => "pending",
            "jobs.garage_id" => $garage->id

        ])->whereBetween('jobs.job_start_date', [$startDate, $monthlyEndDate])
        ->select("jobs.id","jobs.created_at","jobs.updated_at")
            ->get();
        $data["upcoming_second_month_data"] = Job::where([
            "jobs.status" => "pending",
            "jobs.garage_id" => $garage->id

        ])->whereBetween('jobs.job_start_date', [$secondMonthlyStartDate, $secondMonthlyStartDate])
        ->select("jobs.id","jobs.created_at","jobs.updated_at")
            ->get();


            $data["upcoming_first_week_data_count"] = $data["upcoming_first_week_data"]->count();
            $data["upcoming_second_week_data_count"] = $data["upcoming_second_week_data"]->count();
            $data["upcoming_first_month_data_count"] = $data["upcoming_first_month_data"]->count();
            $data["upcoming_second_month_data_count"] = $data["upcoming_second_month_data"]->count();

        return $data;
    }
    public function affiliation_expirings($garage)
    {
        $startDate = now();
        $weeklyEndDate = $startDate->copy()->addDays(7);
        $secondWeeklyStartDate = $startDate->copy()->addDays(8);
        $secondWeeklyEndDate = $startDate->copy()->addDays(14);
        $monthlyEndDate = $startDate->copy()->addDays(30);
        $secondMonthlyStartDate = $startDate->copy()->addDays(31);
        $secondMonthlyStartDate = $startDate->copy()->addDays(60);
        $data["total_data_count"] = GarageAffiliation::where([
            "garage_affiliations.garage_id"=>$garage->id
        ])
        ->count();


        $data["upcoming_first_week_data"] = GarageAffiliation::where([
            "garage_affiliations.garage_id"=>$garage->id
        ])
        ->whereBetween('garage_affiliations.end_date', [$startDate, $weeklyEndDate])

        ->select("garage_affiliations.id","garage_affiliations.created_at","garage_affiliations.updated_at")
            ->get();
        $data["upcoming_second_week_data"] = GarageAffiliation::where([
            "garage_affiliations.garage_id"=>$garage->id
        ])
        ->whereBetween('garage_affiliations.end_date', [$secondWeeklyStartDate, $secondWeeklyEndDate])

        ->select("garage_affiliations.id","garage_affiliations.created_at","garage_affiliations.updated_at")
            ->get();

        $data["upcoming_first_month_data"] = GarageAffiliation::where([
            "garage_affiliations.garage_id"=>$garage->id
        ])
        ->whereBetween('garage_affiliations.end_date', [$startDate, $monthlyEndDate])
        ->select("garage_affiliations.id","garage_affiliations.created_at","garage_affiliations.updated_at")
            ->get();

        $data["upcoming_second_month_data"] = GarageAffiliation::where([
            "garage_affiliations.garage_id"=>$garage->id
        ])
        ->whereBetween('garage_affiliations.end_date', [$secondMonthlyStartDate, $secondMonthlyStartDate])
        ->select("garage_affiliations.id","garage_affiliations.created_at","garage_affiliations.updated_at")
            ->get();

            $data["upcoming_first_week_data_count"] = $data["upcoming_first_week_data"]->count();
            $data["upcoming_second_week_data_count"] = $data["upcoming_second_week_data"]->count();
            $data["upcoming_first_month_data_count"] = $data["upcoming_first_month_data"]->count();
            $data["upcoming_second_month_data_count"] = $data["upcoming_second_month_data"]->count();


        return $data;
    }
}
