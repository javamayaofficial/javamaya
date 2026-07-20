<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = ["name", "phone", "email", "lead_magnet_id", "verified"];
    protected $casts = ["verified" => "boolean"];
}
