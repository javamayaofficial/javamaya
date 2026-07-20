<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payment_gateways')) {
            Schema::create('payment_gateways', function (Blueprint $t) {
                $t->id();
                $t->string('code', 30)->unique();                    // duitku|xendit|moota|manual_transfer|qris_manual
                $t->string('name');
                $t->boolean('active')->default(false);
                $t->boolean('sandbox_mode')->default(true);
                $t->text('credentials')->nullable();                 // JSON encrypted cast
                $t->string('fee_display')->nullable();               // info biaya utk buyer
                $t->unsignedTinyInteger('sort_order')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $t) {
                $t->id();
                $t->string('order_ref', 30)->unique();               // JVM-XXXXXX
                $t->string('idempotency_key', 64)->nullable()->unique(); // anti double-submit checkout
                $t->foreignId('user_id')->nullable()->index();
                $t->string('buyer_name');
                $t->string('buyer_email')->index();
                $t->string('buyer_phone', 20)->index();
                $t->unsignedBigInteger('subtotal');
                $t->unsignedBigInteger('discount')->default(0);
                $t->foreignId('coupon_id')->nullable();
                $t->unsignedBigInteger('tax_amount')->default(0);
                $t->string('tax_mode', 12)->default('inclusive');    // snapshot dari tax_settings
                $t->decimal('tax_percent', 5, 2)->default(0);        // snapshot
                $t->unsignedBigInteger('total');
                $t->unsignedSmallInteger('unique_code')->nullable(); // kode unik nominal transfer (1-999)
                $t->unsignedBigInteger('total_payable');             // total + unique_code
                $t->string('gateway_code', 30)->index();
                $t->enum('status', ['pending', 'paid', 'expired', 'refunded'])->default('pending')->index();
                $t->timestamp('paid_at')->nullable();
                $t->timestamp('expires_at')->nullable()->index();
                $t->string('invoice_number', 30)->nullable()->unique();
                $t->string('invoice_token', 64)->nullable();
                $t->string('affiliate_slug', 30)->nullable()->index();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $t) {
                $t->id();
                $t->foreignId('order_id')->constrained()->cascadeOnDelete();
                $t->foreignId('product_id')->constrained();
                $t->string('product_name');                          // snapshot
                $t->unsignedBigInteger('price');                     // snapshot
                $t->boolean('is_bump')->default(false);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('order_id')->constrained()->cascadeOnDelete();
                $t->string('gateway_code', 30);
                $t->string('gateway_reference')->nullable()->index();
                $t->unsignedBigInteger('amount');
                $t->enum('status', ['pending', 'success', 'failed'])->default('pending');
                $t->json('raw_payload')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('payment_transaction_logs')) {
            Schema::create('payment_transaction_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('order_id')->nullable()->index();
                $t->string('gateway_code', 30)->index();
                $t->string('direction', 10);                          // charge|webhook
                $t->json('payload')->nullable();
                $t->string('result', 30)->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('refunds')) {
            Schema::create('refunds', function (Blueprint $t) {
                $t->id();
                $t->foreignId('order_id')->constrained()->cascadeOnDelete();
                $t->text('reason');
                $t->unsignedBigInteger('amount');
                $t->foreignId('refunded_by')->constrained('users');
                $t->string('gateway_reference')->nullable();
                $t->timestamp('refunded_at');
                $t->timestamps();
            });
        }

        // ---- Webhook reliability ----
        if (! Schema::hasTable('webhook_idempotency')) {
            Schema::create('webhook_idempotency', function (Blueprint $t) {
                $t->id();
                $t->string('gateway', 30);
                $t->string('event_hash', 64);
                $t->timestamp('processed_at')->nullable();
                $t->timestamps();
                $t->unique(['gateway', 'event_hash']);                // kunci anti-dobel di level DB
            });
        }
        if (! Schema::hasTable('webhook_retry_queue')) {
            Schema::create('webhook_retry_queue', function (Blueprint $t) {
                $t->id();
                $t->string('gateway', 30)->index();
                $t->json('payload');
                $t->string('event_hash', 64);
                $t->unsignedTinyInteger('attempts')->default(0);
                $t->text('last_error')->nullable();
                $t->timestamp('next_retry_at')->nullable()->index();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('webhook_dead_letters')) {
            Schema::create('webhook_dead_letters', function (Blueprint $t) {
                $t->id();
                $t->string('gateway', 30)->index();
                $t->json('payload');
                $t->text('last_error')->nullable();
                $t->unsignedTinyInteger('attempts');
                $t->timestamp('reprocessed_at')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('abandoned_cart_logs')) {
            Schema::create('abandoned_cart_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('order_id')->constrained()->cascadeOnDelete();
                $t->unsignedTinyInteger('reminders_sent')->default(0);
                $t->timestamp('last_reminder_at')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['abandoned_cart_logs','webhook_dead_letters','webhook_retry_queue','webhook_idempotency',
                  'refunds','payment_transaction_logs','payments','order_items','orders','payment_gateways'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
