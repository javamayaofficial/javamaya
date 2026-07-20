<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = ['code', 'name', 'active', 'sandbox_mode', 'credentials', 'fee_display', 'sort_order'];
    protected $casts = [
        'active' => 'boolean',
        'sandbox_mode' => 'boolean',
        'credentials' => AsEncryptedArrayObject::class, // encrypted at rest
    ];

    public function credential(string $key, ?string $default = null): ?string
    {
        return $this->credentials[$key] ?? $default;
    }

    /** Gateway siap tampil di checkout: aktif DAN kredensial lengkap (dicek oleh adapternya). */
    public function scopeActive($q) { return $q->where('active', true)->orderBy('sort_order'); }
}
