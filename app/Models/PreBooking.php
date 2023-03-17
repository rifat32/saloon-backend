<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        "customer_id",
        "automobile_make_id",
        "automobile_model_id",
        "car_registration_no",
        "additional_information",
        "status",
        "job_start_date",




        "address",
        "country",
        "city",
        "postcode"




    ];

    public function pre_booking_sub_services(){
        return $this->hasMany(PreBookingSubService::class,'pre_booking_id', 'id');
    }

}
