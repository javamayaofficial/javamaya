<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('affiliates')) {
            Schema::create('affiliates', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $t->string('slug', 30)->unique();
                $t->enum('status', ['active', 'suspended'])->default('active');
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('commission_rules')) {
            Schema::create('commission_rules', function (Blueprint $t) {
                $t->id();
                $t->foreignId('product_id')->nullable()->unique();    // null = global default
                $t->enum('type', ['fixed', 'percentage']);
                $t->unsignedBigInteger('value');
                $t->unsignedSmallInteger('cookie_days')->default(30);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $t) {
                $t->id();
                $t->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
                $t->foreignId('order_id')->unique();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('commissions')) {
            Schema::create('commissions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
                $t->foreignId('order_id')->index();
                $t->unsignedBigInteger('amount');
                $t->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending')->index();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('bank_accounts')) {
            Schema::create('bank_accounts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->nullable()->index();        // null = rekening toko
                $t->string('bank_name');
                $t->string('account_number', 40);
                $t->string('account_holder');
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('payout_requests')) {
            Schema::create('payout_requests', function (Blueprint $t) {
                $t->id();
                $t->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
                $t->foreignId('bank_account_id')->constrained();
                $t->unsignedBigInteger('amount');
                $t->enum('status', ['requested', 'approved', 'paid', 'rejected'])->default('requested')->index();
                $t->string('proof_path')->nullable();                 // bukti transfer
                $t->timestamp('paid_at')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['payout_requests','bank_accounts','commissions','referrals','commission_rules','affiliates'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
