<?php

namespace App\Http\Utils;

use App\Models\Coupon;
use App\Models\GarageTime;
use Carbon\Carbon;
use Exception;

trait BasicUtil
{


    public function validateGarageTimes($garage_id, $dayOfWeek, $job_start_time, $job_end_time = null)
    {
        $garage_time = GarageTime::where([
            "garage_id" => $garage_id
        ])
        ->where('garage_times.day', "=", $dayOfWeek)
        ->where('garage_times.is_closed', "=", 0)
        ->first();

        if (empty($garage_time)) {
            throw new Exception("Garage is not open on this day.");
        }

        $jobStartTime = Carbon::createFromFormat('H:i', $job_start_time)->format('H:i:s');
        $jobStartTime = Carbon::parse($jobStartTime);
        $openingTime = Carbon::parse($garage_time->opening_time);
        $closingTime = Carbon::parse($garage_time->closing_time);

        if ($jobStartTime->lessThan($openingTime) || $jobStartTime->greaterThanOrEqualTo($closingTime)) {
            throw new Exception('The job start time is outside of garage operating hours.', 401);
        }

        if ($job_end_time) {
            $jobEndTime = Carbon::createFromFormat('H:i', $job_end_time)->format('H:i:s');
            $jobEndTime = Carbon::parse($jobEndTime);

            if ($jobEndTime->lessThan($openingTime) || $jobEndTime->greaterThanOrEqualTo($closingTime)) {
                throw new Exception('The job end time is outside of garage operating hours.', 401);
            }
        }
    }


}
