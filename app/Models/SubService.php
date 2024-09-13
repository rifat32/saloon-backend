<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubService extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        "name",
        "description",

        "service_id",
        "is_fixed_price"
        // "is_active",

    ];
    public function service(){
        return $this->belongsTo(Service::class,'service_id', 'id');
    }

    public function garage_automobile_sub_services () {
        return $this->hasMany(GarageSubService::class,"sub_service_id","id");
   }

}
