<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class CanvasPageHtmlRenderer
{
    public static function parse(?string $html): array
    {
        $html = (string) $html;

        $result = [
            'is_full_document' => self::looksLikeFullDocument($html),
            'head_html' => '',
            'body_html' => $html,
            'body_class' => '',
            'body_style' => '',
            'title' => null,
            'meta_desc' => null,
        ];

        if (! $result['is_full_document'] || trim($html) === '') {
            return $result;
        }

        if (! class_exists(DOMDocument::class)) {
            return self::parseWithRegexFallback($html, $result);
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $dom->loadHTML(
                mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR | LIBXML_COMPACT
            );

            if (! $loaded) {
                return self::parseWithRegexFallback($html, $result);
            }

            $head = $dom->getElementsByTagName('head')->item(0);
            $body = $dom->getElementsByTagName('body')->item(0);
            $title = $dom->getElementsByTagName('title')->item(0);

            if ($head instanceof DOMNode) {
                $result['head_html'] = trim(self::filteredHeadHtml($head));
            }

            if ($body instanceof DOMElement) {
                $result['body_html'] = trim(self::innerHtml($body));
                $result['body_class'] = trim((string) $body->getAttribute('class'));
                $result['body_style'] = trim((string) $body->getAttribute('style'));
            }

            if ($title instanceof DOMNode) {
                $result['title'] = trim($title->textContent ?: '');
            }

            foreach ($dom->getElementsByTagName('meta') as $meta) {
                if (! $meta instanceof DOMElement) {
                    continue;
                }

                if (strtolower((string) $meta->getAttribute('name')) === 'description') {
                    $result['meta_desc'] = trim((string) $meta->getAttribute('content'));
                    break;
                }
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $result;
    }

    public static function looksLikeFullDocument(?string $html): bool
    {
        return preg_match('/<\s*!doctype|<\s*html\b|<\s*head\b|<\s*body\b/i', (string) $html) === 1;
    }

    protected static function innerHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }

    protected static function filteredHeadHtml(DOMNode $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $tagName = strtolower($child->tagName);

                if ($tagName === 'title') {
                    continue;
                }

                if ($tagName === 'meta' && strtolower((string) $child->getAttribute('name')) === 'description') {
                    continue;
                }
            }

            $html .= $node->ownerDocument?->saveHTML($child) ?? '';
        }

        return $html;
    }

    protected static function parseWithRegexFallback(string $html, array $result): array
    {
        if (preg_match('/<head\b[^>]*>(.*?)<\/head>/is', $html, $match)) {
            $result['head_html'] = trim($match[1]);
        }

        if (preg_match('/<body\b([^>]*)>(.*?)<\/body>/is', $html, $match)) {
            $bodyAttrs = $match[1] ?? '';
            $result['body_html'] = trim($match[2] ?? '');

            if (preg_match('/class\s*=\s*([\'"])(.*?)\1/is', $bodyAttrs, $classMatch)) {
                $result['body_class'] = trim($classMatch[2]);
            }

            if (preg_match('/style\s*=\s*([\'"])(.*?)\1/is', $bodyAttrs, $styleMatch)) {
                $result['body_style'] = trim($styleMatch[2]);
            }
        }

        if (preg_match('/<title\b[^>]*>(.*?)<\/title>/is', $html, $match)) {
            $result['title'] = trim(strip_tags($match[1]));
        }

        if (preg_match('/<meta\b[^>]*name\s*=\s*([\'"])description\1[^>]*content\s*=\s*([\'"])(.*?)\2/is', $html, $match)
            || preg_match('/<meta\b[^>]*content\s*=\s*([\'"])(.*?)\1[^>]*name\s*=\s*([\'"])description\3/is', $html, $match)) {
            $result['meta_desc'] = trim(end($match));
        }

        $result['head_html'] = trim((string) preg_replace([
            '/<title\b[^>]*>.*?<\/title>/is',
            '/<meta\b[^>]*name\s*=\s*([\'"])description\1[^>]*>/is',
            '/<meta\b[^>]*content\s*=\s*([\'"])(.*?)\1[^>]*name\s*=\s*([\'"])description\3[^>]*>/is',
        ], '', $result['head_html']));

        return $result;
    }
}
