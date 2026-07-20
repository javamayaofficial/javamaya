<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'sku', 'type', 'price', 'compare_price', 'stock', 'cover_path',
        'description', 'product_category_id', 'class_id', 'access_expiry_type',
        'access_expiry_days', 'checkout_template', 'status',
    ];
    protected $casts = ['price' => 'integer', 'compare_price' => 'integer'];

    public function category()  { return $this->belongsTo(ProductCategory::class, 'product_category_id'); }
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function downloads() { return $this->hasMany(ProductDownload::class); }
    public function bumps()     { return $this->hasMany(ProductBump::class); }
    public function bundle()    { return $this->hasOne(ProductBundle::class); }
    public function bundleItems() { return $this->hasManyThrough(ProductBundleItem::class, ProductBundle::class); }
    public function reviews()   { return $this->hasMany(ProductReview::class); }
    public function upsell()    { return $this->hasOne(ProductUpsell::class, 'main_product_id'); }
    public function waitlist()  { return $this->hasMany(ProductWaitlist::class); }

    /** Stok habis? (null = unlimited) */
    public function isSoldOut(): bool
    {
        return $this->stock !== null && (int) $this->stock <= 0;
    }

    public function scopePublished($q) { return $q->where('status', 'published'); }

    /** Hitung expires_at member_access berdasarkan setting produk. */
    public function accessExpiresAt(): ?\Carbon\Carbon
    {
        return match ($this->access_expiry_type) {
            'n_days'            => now()->addDays((int) $this->access_expiry_days),
            'recurring_monthly' => now()->addMonth(),
            'recurring_yearly'  => now()->addYear(),
            default             => null, // lifetime
        };
    }
}
