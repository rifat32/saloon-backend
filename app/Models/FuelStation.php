<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelStation extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        "name",
        "address",
        "opening_time",
        "closing_time",
        "description",
        "is_active"
    ];

}
