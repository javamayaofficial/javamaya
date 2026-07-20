<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_ref', 'idempotency_key', 'user_id', 'buyer_name', 'buyer_email', 'buyer_phone',
        'subtotal', 'discount', 'coupon_id', 'tax_amount', 'tax_mode', 'tax_percent',
        'total', 'unique_code', 'total_payable', 'gateway_code', 'status',
        'paid_at', 'expires_at', 'invoice_number', 'invoice_token', 'affiliate_slug',
    ];
    protected $casts = [
        'paid_at' => 'datetime', 'expires_at' => 'datetime',
        'subtotal' => 'integer', 'discount' => 'integer', 'tax_amount' => 'integer',
        'total' => 'integer', 'total_payable' => 'integer',
    ];

    public function items()    { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function refund()   { return $this->hasOne(Refund::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function coupon()   { return $this->belongsTo(Coupon::class); }
    public function abandonedLog() { return $this->hasOne(AbandonedCartLog::class); }

    public function isPending(): bool { return $this->status === 'pending'; }
    public function isPaid(): bool    { return $this->status === 'paid'; }
}
