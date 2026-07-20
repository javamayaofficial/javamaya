<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = ["path", "size", "type", "storage_driver", "status"];
    protected $casts = ["size" => "integer"];
}
