<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarageTime extends Model
{
    use HasFactory;

    protected $fillable = [
        "day",
        "opening_time",
        "closing_time",
        "garage_id"
    ];
    

}
