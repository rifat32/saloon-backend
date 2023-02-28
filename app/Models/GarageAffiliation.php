<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GarageAffiliation extends Model
{
    use HasFactory;
    protected $fillable = [
        "garage_id",
        "affiliation_id",
    ];



    public function garage(){
        return $this->belongsTo(Garage::class,'garage_id', 'id');
    }
    public function garage_affiliation(){
        return $this->belongsTo(Affiliation::class,'affiliation_id', 'id');
    }
}
