<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateLimitLog extends Model
{
    protected $fillable = ["route", "ip"];
}
