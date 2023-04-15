<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        "garage_id",
        "customer_id",
        "automobile_make_id",
        "automobile_model_id",
        "car_registration_no",
        "car_registration_year",
        "additional_information",
        "status",
        "job_start_date",
        "job_start_time",
        "coupon_code",

        "fuel",
        "transmission",
        // "job_end_date"
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
