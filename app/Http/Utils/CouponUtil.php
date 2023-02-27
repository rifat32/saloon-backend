<?php

namespace App\Http\Utils;

use App\Models\Coupon;
use Carbon\Carbon;
use Exception;

trait CouponUtil
{
    // this function do all the task and returns transaction id or -1
    public function getDiscount($garage_id,$code,$amount)
    {

     $coupon =  Coupon::where([
        "garage_id" => $garage_id,
            "code" => $code,
            "is_active" => 1,

        ])

        ->where('coupon_start_date', '<=', Carbon::now()->subDay())
        ->where('coupon_end_date', '>=', Carbon::now()->subDay())
        ->first();

        if(!$coupon){
            return false;
        }


        if(!empty($coupon->min_total) && ($coupon->min_total > $amount )){
           return false;
        }
        if(!empty($coupon->max_total) && ($coupon->max_total < $amount)){
            return false;
        }

        if(!empty($coupon->redemptions) && $coupon->redemptions > $coupon->customer_redemptions){
            return false;
        }



        return [
            "discount_type" => $coupon->discount_type,
            "discount_amount" => $coupon->discount_amount
        ];


    }
}
