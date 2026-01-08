<?php

declare(strict_types=1);

namespace App\Helpers;

class XssProtectionHelper
{
    private static array $safeHtmlTags = [
        'a', 'abbr', 'address', 'area', 'article', 'aside', 'audio', 'b', 'bdi', 'bdo',
        'blockquote', 'br', 'button', 'canvas', 'caption', 'cite', 'code', 'col', 'colgroup',
        'data', 'datalist', 'dd', 'del', 'details', 'dfn', 'div', 'dl', 'dt', 'em',
        'embed', 'fieldset', 'figcaption', 'figure', 'footer', 'form', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'head', 'header', 'hgroup', 'hr', 'html', 'i', 'iframe',
        'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link', 'main', 'map',
        'mark', 'meta', 'meter', 'nav', 'noscript', 'object', 'ol', 'optgroup', 'option',
        'output', 'p', 'param', 'picture', 'pre', 'progress', 'q', 'rp', 'rt', 'ruby',
        's', 'samp', 'script', 'section', 'select', 'small', 'source', 'span', 'strong',
        'style', 'sub', 'summary', 'sup', 'table', 'tbody', 'td', 'template', 'textarea',
        'tfoot', 'th', 'thead', 'time', 'title', 'tr', 'track', 'u', 'ul', 'var',
        'video', 'wbr',
    ];

    private static array $safeAttributes = [
        'href', 'src', 'alt', 'title', 'class', 'id', 'name', 'value', 'type',
        'placeholder', 'disabled', 'readonly', 'required', 'checked', 'selected',
        'min', 'max', 'step', 'pattern', 'rows', 'cols', 'maxlength',
    ];

    public static function escape(mixed $data): mixed
    {
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        if (is_array($data)) {
            return array_map([self::class, 'escape'], $data);
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                return self::escape($data->toArray());
            }

            return $data;
        }

        return $data;
    }

    public static function escapeJson(mixed $data): string
    {
        $escaped = self::escape($data);

        return json_encode($escaped, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public static function stripTags(string $data, bool $allowBreaks = false): string
    {
        $cleaned = strip_tags($data);

        if ($allowBreaks) {
            $cleaned = nl2br($cleaned);
        }

        return trim($cleaned);
    }

    public static function cleanHtml(string $html): string
    {
        $cleaned = strip_tags($html, '<' . implode('><', self::$safeHtmlTags) . '>');

        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return trim($cleaned);
    }

    public static function sanitizeInput(array $input): array
    {
        $sanitized = [];

        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    public static function sanitizeForAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function sanitizeForUrl(string $url): string
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);

        return htmlspecialchars($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function sanitizeForJavaScript(string $value): string
    {
        $escaped = json_encode($value);

        return substr($escaped, 1, -1);
    }

    public static function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeNumber(float|int|string $value): float|int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return $value;
        }

        $cleaned = preg_replace('/[^0-9.-]/', '', $value);

        return strpos($cleaned, '.') !== false ? (float) $cleaned : (int) $cleaned;
    }

    public static function validateXssAttempts(string $input): bool
    {
        $dangerousPatterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/<iframe\b[^>]*>(.*?)<\/iframe>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<\s*script/i',
            '/<\s*img[^>]+src\s*=\s*["\']javascript:/i',
            '/<\s*body[^>]*on\w+\s*=/i',
            '/<\s*meta[^>]*http-equiv\s*=/i',
            '/eval\s*\(/i',
            '/expression\s*\(/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    public static function detectAndSanitizeXss(string $input): string
    {
        if (self::validateXssAttempts($input)) {
            return self::stripTags($input);
        }

        return $input;
    }
}
