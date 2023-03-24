<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaragePackageSubService extends Model
{
    use HasFactory;
    protected $fillable = [
        "garage_sub_service_id",
        "garage_package_id",
    ];
   
    public function garage_sub_service(){
        return $this->belongsTo(GarageSubService::class,'garage_sub_service_id', 'id');
    }
}
