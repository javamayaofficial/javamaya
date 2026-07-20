<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingPixel extends Model
{
    protected $fillable = ["provider", "pixel_id", "active"];
}
