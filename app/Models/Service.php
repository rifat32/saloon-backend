<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "description",
        "image",
        "automobile_category_id"
        // "is_active",

    ];
    public function category(){
        return $this->belongsTo(AutomobileCategory::class,'automobile_category_id', 'id');
    }
    public function subServices(){
        return $this->hasMany(SubService::class,'service_id', 'id');
    }
}
