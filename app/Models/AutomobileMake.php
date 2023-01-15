<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomobileMake extends Model
{
    use HasFactory;
    protected $fillable = [
        "name",
        "description",
        "automobile_category_id"
        // "is_active",

    ];
    public function category(){
        return $this->belongsTo(AutomobileCategory::class,'automobile_category_id', 'id');
    }


}
