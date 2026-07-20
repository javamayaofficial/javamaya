<?php

namespace App\Services\ContentCMS;

use App\Models\ContentPage;
use League\CommonMark\CommonMarkConverter;

/** Render halaman statis dari markdown, HTML mentah di-escape (aman XSS). */
class StaticPageRenderer
{
    public function render(ContentPage $page): string
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
        return (string) $converter->convert($page->body);
    }

    public function footerPages()
    {
        return ContentPage::where('published', true)->where('show_in_footer', true)
            ->orderBy('title')->get(['title', 'slug']);
    }
}
