<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    protected $fillable = ["api_key_id", "endpoint", "method", "status_code"];
}
