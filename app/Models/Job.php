<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;


    protected $fillable = [
        "garage_id",
        "customer_id",
        "automobile_make_id",
        "automobile_model_id",
        "car_registration_no",
        "status",
        "payment_status",
        "additional_information",
        "discount_type",
        "discount_amount",
        "price",
        "job_start_date",
        "job_start_time",
        "job_end_time",
         "coupon_code",

         "fuel",
         "transmission",
    ];
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
