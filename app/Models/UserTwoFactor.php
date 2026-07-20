<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTwoFactor extends Model
{
    protected $table = 'user_two_factor';
    protected $fillable = ['user_id', 'secret', 'backup_codes', 'confirmed_at'];
    protected $casts = [
        'secret' => 'encrypted',
        'backup_codes' => 'encrypted:array', // array of hashed codes
        'confirmed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
}
