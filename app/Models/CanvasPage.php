<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sales Page satu-kolom (HTML/teks kaya). Bisa jadi homepage atau /l/{slug}.
 * "sections" (JSON) disiapkan untuk canvas builder Tahap 3; kini pakai content_html.
 */
class CanvasPage extends Model
{
    protected $fillable = [
        'title', 'slug', 'content_html', 'sections', 'published',
        'is_homepage', 'meta_title', 'meta_desc', 'show_header', 'show_footer',
    ];

    protected $casts = [
        'sections'     => 'array',
        'published'    => 'boolean',
        'is_homepage'  => 'boolean',
        'show_header'  => 'boolean',
        'show_footer'  => 'boolean',
    ];

    protected static function booted(): void
    {
        // Jaga invariant: hanya SATU halaman yang boleh jadi homepage.
        static::saved(function (CanvasPage $page) {
            if ($page->is_homepage) {
                static::where('id', '!=', $page->id)
                    ->where('is_homepage', true)
                    ->update(['is_homepage' => false]);
            }
        });
    }

    /** Homepage aktif (published + is_homepage), atau null kalau tak ada. */
    public static function activeHomepage(): ?self
    {
        return static::where('is_homepage', true)->where('published', true)->first();
    }
}
