<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('class_categories')) {
            Schema::create('class_categories', function (Blueprint $t) {
                $t->id(); $t->string('name'); $t->string('slug')->unique(); $t->timestamps();
            });
        }

        if (! Schema::hasTable('classes')) {
            Schema::create('classes', function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('slug')->unique();
                $t->foreignId('class_category_id')->nullable()->constrained()->nullOnDelete();
                $t->text('description')->nullable();
                $t->string('cover_path')->nullable();
                $t->boolean('certificate_enabled')->default(true);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('materials')) {
            Schema::create('materials', function (Blueprint $t) {
                $t->id();
                $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $t->string('title');
                $t->enum('type', ['video_url', 'pdf', 'embed', 'text']);
                $t->text('content');                                  // URL video / path pdf / html embed / teks
                $t->unsignedSmallInteger('sort_order')->default(0);
                $t->boolean('is_preview')->default(false);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('material_completions')) {
            Schema::create('material_completions', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->foreignId('material_id')->constrained()->cascadeOnDelete();
                $t->timestamp('completed_at');
                $t->timestamps();
                $t->unique(['user_id', 'material_id']);
            });
        }

        if (! Schema::hasTable('certificates')) {
            Schema::create('certificates', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $t->string('code', 20)->unique();                    // JVM-XXXX-XXXX
                $t->string('participant_name');                      // snapshot nama saat terbit
                $t->string('pdf_path')->nullable();
                $t->timestamp('issued_at');
                $t->timestamps();
                $t->unique(['user_id', 'class_id']);
            });
        }

        if (! Schema::hasTable('membership_levels')) {
            Schema::create('membership_levels', function (Blueprint $t) {
                $t->id(); $t->string('name'); $t->string('slug')->unique();
                $t->unsignedTinyInteger('rank')->default(0);
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('member_access')) {
            Schema::create('member_access', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->string('source_type', 30);                        // product|bundle_item|membership
                $t->unsignedBigInteger('source_id');
                $t->foreignId('product_id')->nullable()->index();     // akses ke produk mana
                $t->foreignId('order_id')->nullable()->index();
                $t->timestamp('starts_at');
                $t->timestamp('expires_at')->nullable()->index();     // null = lifetime
                $t->timestamp('revoked_at')->nullable();
                $t->boolean('expiry_notified')->default(false);
                $t->timestamps();
                $t->index(['user_id', 'product_id']);
            });
        }

        if (! Schema::hasTable('download_tokens')) {
            Schema::create('download_tokens', function (Blueprint $t) {
                $t->id();
                $t->string('token', 64)->unique();
                $t->foreignId('user_id')->constrained()->cascadeOnDelete();
                $t->foreignId('product_download_id')->constrained()->cascadeOnDelete();
                $t->timestamp('expires_at')->index();
                $t->timestamp('used_at')->nullable();
                $t->timestamps();
            });
        }

        if (! Schema::hasTable('download_logs')) {
            Schema::create('download_logs', function (Blueprint $t) {
                $t->id();
                $t->foreignId('user_id')->index();
                $t->foreignId('product_download_id')->index();
                $t->string('ip', 45)->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        foreach (['download_logs','download_tokens','member_access','membership_levels','certificates',
                  'material_completions','materials','classes','class_categories'] as $tbl) {
            Schema::dropIfExists($tbl);
        }
    }
};
