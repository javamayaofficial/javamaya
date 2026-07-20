<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTrustedDevice extends Model
{
    protected $fillable = ['user_id', 'token_hash', 'user_agent', 'ip', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
}
