<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Models\Garage;
use App\Models\PreBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardManagementController extends Controller
{
    use ErrorUtil, GarageUtil;

     /**
        *
     * @OA\Get(
     *      path="/v1.0/garage-owner-dashboard/{garage_id}",
     *      operationId="getGarageOwnerDashboardData",
     *      tags={"dashboard_management"},
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
     *      summary="This method is to get garage owner dashboard",
     *      description="This method is to get garage owner dashboard",
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

    public function getGarageOwnerDashboardData($garage_id,Request $request) {
$garage = Garage::where([
    "id" => $garage_id,
    "owner_id" => $request->user()->id
])
->first();
if(!$garage){
    return response()->json([
        "message" => "you are not the owner of the garage or the request garage does not exits"
    ],404);
}

$data["pre_booking_count"] = PreBooking::leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')
->where([
    "pre_bookings.city" => $garage->city
])
->whereNotIn('job_bids.garage_id', [$garage->id])
->where('pre_bookings.status',"pending")
->groupBy("pre_bookings.id")
->select(
    "pre_bookings.*",
    DB::raw('COUNT(job_bids.id) AS bid_count')
)

->orderByDesc("pre_bookings.id")

->havingRaw('bid_count < 4')
->count();
















    }
}
