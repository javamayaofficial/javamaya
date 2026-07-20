<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $fillable = ["user_id", "session_id", "device", "ip", "last_active_at", "revoked_at"];
    protected $casts = ["last_active_at" => "datetime", "revoked_at" => "datetime"];
    public function user() { return $this->belongsTo(User::class); }
}
