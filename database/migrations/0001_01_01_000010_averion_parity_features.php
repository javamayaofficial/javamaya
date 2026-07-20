<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** v1.1.0: Upsell pasca-beli, Waitlist produk, Trusted Devices 2FA. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_upsells')) {
            Schema::create('product_upsells', function (Blueprint $t) {
                $t->id();
                $t->foreignId('main_product_id')->unique()->constrained('products')->cascadeOnDelete();
                $t->foreignId('upsell_product_id')->constrained('products')->cascadeOnDelete();
                $t->unsignedBigInteger('upsell_price');          // harga spesial penawaran
                $t->string('headline', 200);
                $t->text('description')->nullable();
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('product_waitlist')) {
            Schema::create('product_waitlist', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->constrained()->cascadeOnDelete();
                $t->foreignId('user_id')->nullable();
                $t->string('name', 100);
                $t->string('whatsapp', 20);
                $t->string('email', 150)->nullable();
                $t->string('ip', 45)->nullable();
                $t->enum('status', ['waiting', 'notified', 'converted'])->default('waiting')->index();
                $t->timestamp('notified_at')->nullable();
                $t->timestamps();
                $t->unique(['product_id', 'whatsapp']);
            });
        }

        if (! Schema::hasTable('user_trusted_devices')) {
            Schema::create('user_trusted_devices', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->char('token_hash', 64)->unique();
                $t->string('user_agent', 255)->nullable();
                $t->string('ip', 45)->nullable();
                $t->dateTime('expires_at')->index();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['user_trusted_devices', 'product_waitlist', 'product_upsells'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
