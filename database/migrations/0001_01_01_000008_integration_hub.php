<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('key_hash', 64)->unique();                 // sha256 dari key
                $t->string('key_prefix', 12);                         // utk display "jvm_ab12..."
                $t->json('scopes');                                   // ["products:read","orders:read",...]
                $t->unsignedSmallInteger('rate_limit_per_minute')->default(60);
                $t->timestamp('last_used_at')->nullable();
                $t->timestamp('revoked_at')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('api_usage_logs')) {
            Schema::create('api_usage_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('api_key_id')->constrained()->cascadeOnDelete();
                $t->string('endpoint', 120);
                $t->string('method', 10);
                $t->unsignedSmallInteger('status_code');
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('outbound_webhook_subscriptions')) {
            Schema::create('outbound_webhook_subscriptions', function (Blueprint $t) {
                $t->id();
                $t->string('event', 60)->index();                     // order.completed | order.refunded
                $t->string('url', 500);
                $t->string('secret', 64);                             // HMAC secret
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('outbound_webhook_deliveries')) {
            Schema::create('outbound_webhook_deliveries', function (Blueprint $t) {
                $t->id();
                $t->foreignId('subscription_id')->constrained('outbound_webhook_subscriptions')->cascadeOnDelete();
                $t->string('event', 60);
                $t->json('payload');
                $t->unsignedTinyInteger('attempt_count')->default(0);
                $t->enum('status', ['pending', 'success', 'retrying', 'dead'])->default('pending')->index();
                $t->unsignedSmallInteger('response_code')->nullable();
                $t->text('last_error')->nullable();
                $t->timestamp('next_retry_at')->nullable()->index();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['outbound_webhook_deliveries','outbound_webhook_subscriptions','api_usage_logs','api_keys'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
