<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'tag',
        "garage_id",
        "is_default"
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        "garage_id",
    ];
}
