<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductWaitlist extends Model
{
    protected $table = 'product_waitlist';
    protected $fillable = ['product_id', 'user_id', 'name', 'whatsapp', 'email', 'ip', 'status', 'notified_at'];
    protected $casts = ['notified_at' => 'datetime'];

    public function product() { return $this->belongsTo(Product::class); }
}
