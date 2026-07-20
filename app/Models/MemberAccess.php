<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberAccess extends Model
{
    protected $table = "member_access";
    protected $fillable = ["user_id", "source_type", "source_id", "product_id", "order_id", "starts_at", "expires_at", "revoked_at", "expiry_notified"];
    protected $casts = ["starts_at" => "datetime", "expires_at" => "datetime", "revoked_at" => "datetime", "expiry_notified" => "boolean"];
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function isActive(): bool
    {
        return $this->revoked_at === null && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
