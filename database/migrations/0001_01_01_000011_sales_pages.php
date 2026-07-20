<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * v1.3.0: Sales Page satu-kolom (HTML/teks kaya).
 * Memakai tabel canvas_pages yang sudah disiapkan, menambah kolom yang diperlukan.
 * Satu halaman bisa dipilih jadi homepage (is_homepage) atau tampil di /l/{slug}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canvas_pages', function (Blueprint $t) {
            if (! Schema::hasColumn('canvas_pages', 'content_html'))  $t->longText('content_html')->nullable()->after('slug');
            if (! Schema::hasColumn('canvas_pages', 'is_homepage'))   $t->boolean('is_homepage')->default(false)->index()->after('published');
            if (! Schema::hasColumn('canvas_pages', 'meta_title'))    $t->string('meta_title')->nullable()->after('is_homepage');
            if (! Schema::hasColumn('canvas_pages', 'meta_desc'))     $t->string('meta_desc', 300)->nullable()->after('meta_title');
            if (! Schema::hasColumn('canvas_pages', 'show_header'))   $t->boolean('show_header')->default(true)->after('meta_desc');
            if (! Schema::hasColumn('canvas_pages', 'show_footer'))   $t->boolean('show_footer')->default(true)->after('show_header');
        });
    }

    public function down(): void
    {
        Schema::table('canvas_pages', function (Blueprint $t) {
            foreach (['content_html', 'is_homepage', 'meta_title', 'meta_desc', 'show_header', 'show_footer'] as $col) {
                if (Schema::hasColumn('canvas_pages', $col)) $t->dropColumn($col);
            }
        });
    }
};
