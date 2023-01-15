<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomobileModel extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "description",
        "automobile_make_id"
        // "is_active",

    ];
    public function make(){
        return $this->belongsTo(AutomobileMake::class,'automobile_make_id', 'id');
    }
}
