<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Garage extends Model
{
    use HasFactory,  SoftDeletes;
    protected $fillable = [
        "name",
        "about",
        "web_page",
        "phone",
        "email",
        "additional_information",
        "address_line_1",
        "address_line_2",
        "lat",
        "long",
        "country",
        "city",
        "postcode",
        "logo",
        "status",
        // "is_active",
        "is_mobile_garage",
        "wifi_available",
        "labour_rate",
        "average_time_slot",
        "owner_id",
        "created_by",
    ];

    public function owner(){
        return $this->belongsTo(User::class,'owner_id', 'id');
    }

    public function garageServices(){
        return $this->hasMany(GarageService::class,'garage_id', 'id');
    }

    public function garageAutomobileMakes(){
        return $this->hasMany(GarageAutomobileMake::class,'garage_id', 'id');
    }

    public function garageGalleries(){
        return $this->hasMany(GarageGallery::class,'garage_id', 'id');
    }

    public function garage_times(){
        return $this->hasMany(GarageTime::class,'garage_id', 'id');
    }

































































}
