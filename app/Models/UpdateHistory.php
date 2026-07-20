<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpdateHistory extends Model
{
    protected $table = "update_history";
    protected $fillable = ["from_version", "to_version", "status", "duration_seconds", "initiated_by", "log_excerpt"];
}
