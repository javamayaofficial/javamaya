<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('phone', 20)->index();
                $t->string('email')->nullable();
                $t->foreignId('lead_magnet_id')->nullable()->index();
                $t->boolean('verified')->default(false);
                $t->timestamps();
                $t->unique(['phone', 'lead_magnet_id']);
            });
        }
        if (! Schema::hasTable('lead_magnets')) {
            Schema::create('lead_magnets', function (Blueprint $t) {
                $t->id(); $t->string('title'); $t->string('slug')->unique();
                $t->string('deliverable_url')->nullable();
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('email_sequences')) {
            Schema::create('email_sequences', function (Blueprint $t) {
                $t->id(); $t->string('name');
                $t->string('trigger_type', 30)->default('manual');    // manual|purchase_product|lead_magnet
                $t->unsignedBigInteger('trigger_id')->nullable();
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('email_sequence_steps')) {
            Schema::create('email_sequence_steps', function (Blueprint $t) {
                $t->id();
                $t->foreignId('email_sequence_id')->constrained()->cascadeOnDelete();
                $t->unsignedSmallInteger('delay_hours')->default(24);
                $t->string('subject');
                $t->longText('body');
                $t->unsignedSmallInteger('sort_order')->default(0);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('email_sequence_enrollments')) {
            Schema::create('email_sequence_enrollments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('email_sequence_id')->constrained()->cascadeOnDelete();
                $t->string('email')->index();
                $t->unsignedSmallInteger('current_step')->default(0);
                $t->timestamp('next_send_at')->nullable()->index();
                $t->timestamp('unsubscribed_at')->nullable();
                $t->timestamps();
                $t->unique(['email_sequence_id', 'email']);
            });
        }
        if (! Schema::hasTable('email_sequence_sends')) {
            Schema::create('email_sequence_sends', function (Blueprint $t) {
                $t->id();
                $t->foreignId('email_sequence_enrollment_id')->constrained()->cascadeOnDelete();
                $t->foreignId('email_sequence_step_id')->constrained()->cascadeOnDelete();
                $t->string('status', 20)->default('sent');
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('broadcast_campaigns')) {
            Schema::create('broadcast_campaigns', function (Blueprint $t) {
                $t->id(); $t->string('name');
                $t->enum('channel', ['wa', 'email']);
                $t->string('subject')->nullable();
                $t->longText('body');
                $t->json('segment')->nullable();
                $t->enum('status', ['draft', 'sending', 'paused', 'done'])->default('draft');
                $t->unsignedSmallInteger('throttle_per_minute')->default(20);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('broadcast_recipients')) {
            Schema::create('broadcast_recipients', function (Blueprint $t) {
                $t->id();
                $t->foreignId('broadcast_campaign_id')->constrained()->cascadeOnDelete();
                $t->string('recipient');
                $t->string('status', 20)->default('queued')->index();
                $t->text('error')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('notification_templates')) {
            Schema::create('notification_templates', function (Blueprint $t) {
                $t->id();
                $t->string('key', 60);                                // order_created, payment_confirmed, dst
                $t->enum('channel', ['wa', 'email']);
                $t->string('subject')->nullable();
                $t->longText('body');
                $t->timestamps();
                $t->unique(['key', 'channel']);
            });
        }
        if (! Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $t) {
                $t->id();
                $t->enum('channel', ['wa', 'email']);
                $t->string('recipient');
                $t->string('template_key', 60)->nullable();
                $t->string('status', 20)->default('sent')->index();
                $t->text('error')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('integration_logs')) {
            Schema::create('integration_logs', function (Blueprint $t) {
                $t->id();
                $t->string('provider', 40)->index();
                $t->string('action', 60);
                $t->json('request')->nullable();
                $t->json('response')->nullable();
                $t->boolean('success')->default(true)->index();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('funnel_events')) {
            Schema::create('funnel_events', function (Blueprint $t) {
                $t->id();
                $t->string('stage', 30)->index();                     // page_view..payment_completed
                $t->foreignId('product_id')->nullable()->index();
                $t->string('session_hash', 64)->nullable();
                $t->date('event_date')->index();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('tracking_pixels')) {
            Schema::create('tracking_pixels', function (Blueprint $t) {
                $t->id(); $t->string('provider', 30); $t->string('pixel_id');
                $t->boolean('active')->default(true);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('canvas_pages')) {                      // Tahap 3 (schema disiapkan)
            Schema::create('canvas_pages', function (Blueprint $t) {
                $t->id(); $t->string('title'); $t->string('slug')->unique();
                $t->json('sections')->nullable();
                $t->boolean('published')->default(false);
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('ai_chat_rate_limits')) {               // Tahap 3 (schema disiapkan)
            Schema::create('ai_chat_rate_limits', function (Blueprint $t) {
                $t->id(); $t->string('visitor_hash', 64)->index();
                $t->unsignedInteger('count')->default(0);
                $t->date('window_date');
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['ai_chat_rate_limits','canvas_pages','tracking_pixels','funnel_events','integration_logs',
                  'notification_logs','notification_templates','broadcast_recipients','broadcast_campaigns',
                  'email_sequence_sends','email_sequence_enrollments','email_sequence_steps','email_sequences',
                  'lead_magnets','leads'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
