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
        "additional_information",
        "status",
    ];




    public function garage(){
        return $this->belongsTo(Garage::class,'garage_id', 'id');
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id', 'id');
    }
    public function booking_sub_services(){
        return $this->hasMany(BookingSubService::class,'booking_id', 'id');
    }









}
