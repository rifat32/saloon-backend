<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [

        "expert_id",
        "booked_slots",


        "pre_booking_id",
        "garage_id",
        "booking_id",
        "customer_id",
        "additional_information",
        "status",
        "coupon_code",
        "job_start_date",
        "price",
        "discount_type",
        "discount_amount",
        "created_by",
        "created_from"

    ];




    public function garage(){
        return $this->belongsTo(Garage::class,'garage_id', 'id')->withTrashed();
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id', 'id')->withTrashed();
    }

    public function automobile_make(){
        return $this->belongsTo(AutomobileMake::class,'automobile_make_id', 'id')->withTrashed();
    }
    public function automobile_model(){
        return $this->belongsTo(AutomobileModel::class,'automobile_model_id', 'id')->withTrashed();
    }

    public function booking_sub_services(){
        return $this->hasMany(BookingSubService::class,'booking_id', 'id');
    }


    public function booking_packages(){
        return $this->hasMany(BookingPackage::class,'booking_id', 'id');
    }







}
