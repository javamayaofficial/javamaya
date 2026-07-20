<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_categories')) {
            Schema::create('product_categories', function (Blueprint $t) {
                $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->timestamps();
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('slug')->unique();
                $t->string('sku', 60)->nullable();
                $t->enum('type', ['digital', 'downloadable', 'class', 'bundle'])->index();
                $t->unsignedBigInteger('price');                     // integer rupiah
                $t->unsignedBigInteger('compare_price')->nullable();
                $t->unsignedInteger('stock')->nullable();            // null = unlimited
                $t->string('cover_path')->nullable();
                $t->longText('description')->nullable();
                $t->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
                $t->foreignId('class_id')->nullable();               // untuk type=class
                $t->enum('access_expiry_type', ['lifetime', 'n_days', 'recurring_monthly', 'recurring_yearly'])->default('lifetime');
                $t->unsignedSmallInteger('access_expiry_days')->nullable(); // wajib bila n_days
                $t->string('checkout_template', 30)->nullable();     // null = global default
                $t->enum('status', ['draft', 'published'])->default('draft')->index();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('product_bundles')) {
            Schema::create('product_bundles', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->unique()->constrained()->cascadeOnDelete(); // produk type=bundle
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('product_bundle_items')) {
            Schema::create('product_bundle_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_bundle_id')->constrained()->cascadeOnDelete();
                $t->foreignId('item_product_id')->constrained('products')->cascadeOnDelete();
                $t->timestamps();
                $t->unique(['product_bundle_id', 'item_product_id']);
            });
        }

        if (! Schema::hasTable('product_downloads')) {
            Schema::create('product_downloads', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->constrained()->cascadeOnDelete();
                $t->string('label');
                $t->string('file_path');                             // storage/app/downloads (luar publik)
                $t->unsignedBigInteger('file_size')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('product_bumps')) {
            Schema::create('product_bumps', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->constrained()->cascadeOnDelete();       // produk utama
                $t->foreignId('bump_product_id')->constrained('products')->cascadeOnDelete();
                $t->unsignedBigInteger('bump_price');
                $t->string('headline');
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('product_reviews')) {
            Schema::create('product_reviews', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->constrained()->cascadeOnDelete();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->unsignedTinyInteger('rating');                   // 1-5
                $t->text('body')->nullable();
                $t->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
                $t->timestamps();
                $t->unique(['product_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('product_licenses')) {                 // bila buyer menjual lisensi
            Schema::create('product_licenses', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->constrained()->cascadeOnDelete();
                $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $t->foreignId('order_id')->nullable()->index();
                $t->string('license_key')->unique();
                $t->timestamp('expires_at')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $t) {
                $t->id();
                $t->string('code', 40)->unique();
                $t->enum('type', ['fixed', 'percentage']);
                $t->unsignedBigInteger('value');                     // rupiah atau persen 1-100
                $t->unsignedInteger('max_uses_total')->nullable();
                $t->unsignedInteger('max_uses_per_user')->nullable();
                $t->json('product_ids')->nullable();                 // null = semua produk
                $t->json('excluded_product_ids')->nullable();
                $t->timestamp('starts_at')->nullable();
                $t->timestamp('expires_at')->nullable();
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $t) {
                $t->id();
                $t->foreignId('coupon_id')->constrained()->cascadeOnDelete();
                $t->foreignId('order_id')->index();
                $t->foreignId('user_id')->nullable()->index();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['coupon_usages','coupons','product_licenses','product_reviews','product_bumps',
                  'product_downloads','product_bundle_items','product_bundles','products','product_categories'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
