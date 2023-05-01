<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "sku",
        "description",
        "image",
        // "is_active",
        "is_default",
        "shop_id"

    ];
    public function product_variations(){
        return $this->hasMany(ProductVariation::class,'product_id', 'id');
    }

}
