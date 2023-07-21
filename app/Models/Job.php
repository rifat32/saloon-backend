<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory,SoftDeletes;


    protected $fillable = [
        "booking_id",
        "garage_id",
        "customer_id",
        "automobile_make_id",
        "automobile_model_id",
        "car_registration_no",
        "car_registration_year",
        "status",
        "payment_status",
        "additional_information",
        "discount_type",
        "discount_amount",
        "coupon_discount_type",
        "coupon_discount_amount",
        "price",
        "final_price",
        "job_start_date",
        "job_start_time",
        "job_end_time",
         "coupon_code",

         "fuel",
         "transmission",
    ];
    public function booking(){
        return $this->belongsTo(Booking::class,'booking_id', 'id');
    }
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

    public function job_sub_services(){
        return $this->hasMany(JobSubService::class,'job_id', 'id');
    }
    public function job_packages(){
        return $this->hasMany(JobPackage::class,'job_id', 'id');
    }
    public function job_payments(){
        return $this->hasMany(JobPayment::class,'job_id', 'id');
    }
}
