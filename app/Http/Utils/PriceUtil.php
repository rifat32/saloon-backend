<?php

namespace App\Http\Utils;

use App\Models\Coupon;
use App\Models\GarageSubServicePrice;
use App\Models\Service;
use App\Models\SubService;
use Carbon\Carbon;
use Exception;

trait PriceUtil
{
    // this function do all the task and returns transaction id or -1
    public function getPrice($sub_service_id,$garage_sub_service_id,$automobile_make_id)
    {

        $sub_service = SubService::where([
            "id" => $sub_service_id
        ])
        ->first();



if($sub_service->is_fixed_price){

    $garage_sub_service_price = GarageSubServicePrice::where([
        "garage_sub_service_id" => $garage_sub_service_id,
        "automobile_make_id" => NULL,
    ])
    ->first();
    if($garage_sub_service_price) {
        return  $garage_sub_service_price->price;
    }

}
else{
    $garage_sub_service_price = GarageSubServicePrice::where([
        "garage_sub_service_id" => $garage_sub_service_id,
        "automobile_make_id" => $automobile_make_id,
    ])
    ->first();


    if($garage_sub_service_price) {
        return  $garage_sub_service_price->price;
    }




    $garage_sub_service_price = GarageSubServicePrice::where([
        "garage_sub_service_id" => $garage_sub_service_id,
        "automobile_make_id" => NULL,
    ])
    ->first();

    if($garage_sub_service_price) {
        return  $garage_sub_service_price->price;
    }

}

return 0;

    }





}
