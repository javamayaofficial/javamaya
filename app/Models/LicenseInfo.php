<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseInfo extends Model
{
    protected $table = "license_info";
    protected $fillable = ["license_key", "status", "validated_at", "grace_until"];
    protected $casts = ["validated_at" => "datetime", "grace_until" => "datetime"];
}
