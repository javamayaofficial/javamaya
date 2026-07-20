<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Semua migration Javamaya IDEMPOTENT: dicek hasTable/hasColumn sebelum create. */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('email')->unique();
                $t->string('phone', 20)->nullable()->index();      // format 62xxx
                $t->string('password')->nullable();                 // nullable: user OTP/Google only
                $t->string('google_id')->nullable()->index();
                $t->enum('role', ['super_admin', 'staff', 'customer'])->default('customer')->index();
                $t->boolean('is_affiliate')->default(false);
                $t->timestamp('email_verified_at')->nullable();
                $t->rememberToken();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('staff_permissions')) {
            Schema::create('staff_permissions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->string('permission', 100);                      // ex: transactions.read, refunds.manage
                $t->timestamps();
                $t->unique(['user_id', 'permission']);
            });
        }

        if (! Schema::hasTable('user_two_factor')) {
            Schema::create('user_two_factor', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $t->text('secret');                                 // encrypted cast
                $t->text('backup_codes');                           // JSON hashed, encrypted cast
                $t->timestamp('confirmed_at')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('user_sessions')) {
            Schema::create('user_sessions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->string('session_id')->index();
                $t->string('device')->nullable();
                $t->string('ip', 45)->nullable();
                $t->timestamp('last_active_at')->nullable();
                $t->timestamp('revoked_at')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('otp_codes')) {
            Schema::create('otp_codes', function (Blueprint $t) {
                $t->id();
                $t->string('phone', 20)->index();
                $t->string('code_hash');
                $t->string('purpose', 30)->default('login');
                $t->unsignedTinyInteger('attempts')->default(0);
                $t->timestamp('expires_at');
                $t->timestamp('used_at')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('login_attempts')) {
            Schema::create('login_attempts', function (Blueprint $t) {
                $t->id();
                $t->string('identifier')->index();                  // email atau phone
                $t->string('ip', 45)->index();
                $t->boolean('success')->default(false);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->nullable()->index();
                $t->string('action', 100)->index();                 // ex: refund.marked, session.revoked
                $t->string('subject_type')->nullable();
                $t->unsignedBigInteger('subject_id')->nullable();
                $t->json('meta')->nullable();
                $t->string('ip', 45)->nullable();
                $t->timestamps();
                $t->index(['subject_type', 'subject_id']);
            });
        }

        // Tabel bawaan Laravel yang wajib ada di shared hosting (session/cache/queue database driver)
        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $t) {
                $t->string('id')->primary();
                $t->foreignId('user_id')->nullable()->index();
                $t->string('ip_address', 45)->nullable();
                $t->text('user_agent')->nullable();
                $t->longText('payload');
                $t->integer('last_activity')->index();
            });
        }
        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $t) {
                $t->string('key')->primary();
                $t->mediumText('value');
                $t->integer('expiration');
            });
        }
        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $t) {
                $t->string('key')->primary();
                $t->string('owner');
                $t->integer('expiration');
            });
        }
        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $t) {
                $t->id();
                $t->string('queue')->index();
                $t->longText('payload');
                $t->unsignedTinyInteger('attempts');
                $t->unsignedInteger('reserved_at')->nullable();
                $t->unsignedInteger('available_at');
                $t->unsignedInteger('created_at');
            });
        }
        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $t) {
                $t->id();
                $t->string('uuid')->unique();
                $t->text('connection');
                $t->text('queue');
                $t->longText('payload');
                $t->longText('exception');
                $t->timestamp('failed_at')->useCurrent();
            });
        }
        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $t) {
                $t->id();
                $t->morphs('tokenable');
                $t->string('name');
                $t->string('token', 64)->unique();
                $t->text('abilities')->nullable();
                $t->timestamp('last_used_at')->nullable();
                $t->timestamp('expires_at')->nullable();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $t) {
                $t->string('email')->primary();
                $t->string('token');
                $t->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach (['staff_permissions','user_two_factor','user_sessions','otp_codes','login_attempts','activity_logs'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
