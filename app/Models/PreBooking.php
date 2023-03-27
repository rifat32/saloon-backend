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
        "job_start_time",


        "address",
        "country",
        "city",
        "postcode"




    ];

    public function pre_booking_sub_services(){
        return $this->hasMany(PreBookingSubService::class,'pre_booking_id', 'id');
    }
    public function job_bids(){
        return $this->hasMany(JobBid::class,'pre_booking_id', 'id');
    }
}
