<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GdprRequest extends Model
{
    protected $fillable = ["user_id", "type", "status", "export_path", "export_expires_at", "cooling_until", "processed_at"];
    protected $casts = ["export_expires_at" => "datetime", "cooling_until" => "datetime", "processed_at" => "datetime"];
    public function user() { return $this->belongsTo(User::class); }
}
