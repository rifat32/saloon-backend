<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        "pre_booking_id",
        "garage_id",
        "booking_id",
        "customer_id",
        "automobile_make_id",
        "automobile_model_id",
        "car_registration_no",
        "car_registration_year",
        "additional_information",
        "status",

        "coupon_code",

        "fuel",
        "transmission",

        "job_start_date",
        "job_start_time",
        "job_end_time",
        "price",
        "discount_type",
        "discount_amount",
        "created_by",
        "created_from"

    ];
    



    public function garage(){
        return $this->belongsTo(Garage::class,'garage_id', 'id');
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id', 'id');
    }

    public function automobile_make(){
        return $this->belongsTo(AutomobileMake::class,'automobile_make_id', 'id');
    }
    public function automobile_model(){
        return $this->belongsTo(AutomobileModel::class,'automobile_model_id', 'id');
    }

    public function booking_sub_services(){
        return $this->hasMany(BookingSubService::class,'booking_id', 'id');
    }


    public function booking_packages(){
        return $this->hasMany(BookingPackage::class,'booking_id', 'id');
    }






}
