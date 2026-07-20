<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $t) {
                $t->id();
                $t->string('key', 100)->unique();
                $t->text('value')->nullable();
                $t->boolean('encrypted')->default(false);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('tax_settings')) {
            Schema::create('tax_settings', function (Blueprint $t) {
                $t->id();
                $t->decimal('percentage', 5, 2)->default(11.00);
                $t->enum('mode', ['inclusive', 'exclusive'])->default('inclusive');
                $t->string('npwp', 30)->nullable();
                $t->text('store_address')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('content_pages')) {
            Schema::create('content_pages', function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('slug')->unique();
                $t->longText('body');                                 // markdown
                $t->boolean('published')->default(true);
                $t->boolean('show_in_footer')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('gdpr_requests')) {
            Schema::create('gdpr_requests', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->enum('type', ['export', 'delete']);
                $t->enum('status', ['requested', 'processing', 'ready', 'done', 'cancelled'])->default('requested')->index();
                $t->string('export_path')->nullable();
                $t->timestamp('export_expires_at')->nullable();
                $t->timestamp('cooling_until')->nullable();           // delete: 30 hari
                $t->timestamp('processed_at')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('cron_heartbeats')) {
            Schema::create('cron_heartbeats', function (Blueprint $t) {
                $t->id();
                $t->string('source', 20)->default('visit');           // visit|cron
                $t->timestamp('last_ran_at');
                $t->unsignedInteger('jobs_processed')->default(0);
                $t->unsignedInteger('errors_count')->default(0);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('backups')) {
            Schema::create('backups', function (Blueprint $t) {
                $t->id();
                $t->string('path');
                $t->unsignedBigInteger('size')->default(0);
                $t->enum('type', ['db', 'uploads', 'full'])->default('db');
                $t->string('storage_driver', 20)->default('local');
                $t->string('status', 20)->default('success');
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('rate_limit_logs')) {
            Schema::create('rate_limit_logs', function (Blueprint $t) {
                $t->id();
                $t->string('route', 100)->index();
                $t->string('ip', 45);
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['rate_limit_logs','backups','cron_heartbeats','gdpr_requests','content_pages','tax_settings','settings'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
