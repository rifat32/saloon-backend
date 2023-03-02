<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarageSubServicePrice extends Model
{
    use HasFactory;
    protected $fillable = [
        "garage_sub_service_id",
        "automobile_make_id",
        "price"
    ];

    public function garage_sub_service(){
        return $this->belongsTo(GarageSubService::class,'garage_sub_service_id', 'id');
    }
    public function automobile_make(){
        return $this->belongsTo(SubService::class,'automobile_make_id', 'id');
    }

}
