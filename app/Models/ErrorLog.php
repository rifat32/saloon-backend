<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
    use HasFactory;

    protected $connection = 'logs';
    protected $fillable = [
        "api_url",
        "user",
        "message",
        "status_code",
        "line",
        "file",

    ];
}
