<?php

declare(strict_types=1);

namespace App\Support;

final class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><s><u><h2><h3><h4><ul><ol><li><blockquote><pre><code><hr>';

    public static function toSafeHtml(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        if (! preg_match('/<\/?[a-z][\s\S]*>/i', $content)) {
            return nl2br(e($content), false);
        }

        $stripped = strip_tags($content, self::ALLOWED_TAGS);

        // Remove inline event handlers and style attributes on allowed tags.
        return (string) preg_replace(
            '/\s+(on\w+|style)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i',
            '',
            $stripped
        );
    }

    public static function toPlainText(?string $content): string
    {
        if ($content === null || trim($content) === '') {
            return '';
        }

        return trim(html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
