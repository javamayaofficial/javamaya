<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('update_history')) {
            Schema::create('update_history', function (Blueprint $t) {
                $t->id();
                $t->string('from_version', 20);
                $t->string('to_version', 20);
                $t->enum('status', ['success', 'failed', 'rolled_back', 'manual_detected'])->index();
                $t->unsignedInteger('duration_seconds')->nullable();
                $t->foreignId('initiated_by')->nullable();
                $t->text('log_excerpt')->nullable();
                $t->timestamps();
            });
        }
        // Tahap 3 — schema disiapkan agar migration path mulus
        if (! Schema::hasTable('license_info')) {
            Schema::create('license_info', function (Blueprint $t) {
                $t->id();
                $t->string('license_key')->nullable();
                $t->string('status', 20)->default('unlicensed');
                $t->timestamp('validated_at')->nullable();
                $t->timestamp('grace_until')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('license_api_logs')) {
            Schema::create('license_api_logs', function (Blueprint $t) {
                $t->id();
                $t->string('action', 30);
                $t->json('response')->nullable();
                $t->boolean('success')->default(true);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['license_api_logs','license_info','update_history'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
