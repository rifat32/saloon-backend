<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaragePackage extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "description",
        "price",
        "garage_id"
    ];

    public function garage(){
        return $this->belongsTo(Garage::class,'garage_id', 'id');
    }
    public function garage_package_sub_service(){
        return $this->hasMany(GaragePackage::class,'garage_package_id', 'id');
    }
}
