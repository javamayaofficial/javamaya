<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $fillable = ["user_id", "slug", "status"];
    public function user() { return $this->belongsTo(User::class); }
    public function commissions() { return $this->hasMany(Commission::class); }
    public function approvedBalance(): int
    {
        return (int) $this->commissions()->where("status", "approved")->sum("amount");
    }
}
