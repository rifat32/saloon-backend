<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPayment extends Model
{
    use HasFactory;
    protected $fillable = [
        "job_id",
        "payment_type_id",
        "amount",
    ];


    public function job(){
        return $this->belongsTo(Job::class,'job_id', 'id');
    }



}
