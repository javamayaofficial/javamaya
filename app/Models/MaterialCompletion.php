<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialCompletion extends Model
{
    protected $fillable = ["user_id", "material_id", "completed_at"];
    protected $casts = ["completed_at" => "datetime"];
}
