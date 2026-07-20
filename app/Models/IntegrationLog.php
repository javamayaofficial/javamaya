<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = ["provider", "action", "request", "response", "success"];
    protected $casts = ["request" => "array", "response" => "array", "success" => "boolean"];
}
