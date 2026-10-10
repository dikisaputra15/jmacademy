<?php

namespace App\Support;

use DOMDocument;
use DOMNode;
use Illuminate\Support\Str;

class LessonResources
{
    public static function render(?string $value): string
    {
        if (! filled($value)) {
            return '';
        }
        if (! preg_match('/<(?:p|div|h[1-6]|ul|ol|table|a|strong|span|br)\b/i', $value)) {
            $value = Str::markdown($value, ['html_input' => 'strip', 'allow_unsafe_links' => false]);
        }
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$value.'</body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $body = $document->getElementsByTagName('body')->item(0);
        return $body ? self::children($body) : '';
    }

    private static function children(DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $html .= htmlspecialchars($child->nodeValue, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }
            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'img', 'form', 'input'], true)) {
                continue;
            }
            $content = self::children($child);
            if (! in_array($tag, ['p', 'div', 'span', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'a', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'hr'], true)) {
                $html .= $content;
                continue;
            }
            $attributes = '';
            if ($tag === 'a') {
                $url = trim($child->getAttribute('href'));
                if (preg_match('~^https?://~i', $url) && filter_var($url, FILTER_VALIDATE_URL)) {
                    $attributes = ' href="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener noreferrer"';
                }
            }
            $styles = [];
            foreach (explode(';', $child->getAttribute('style')) as $style) {
                if (preg_match('/^\s*(text-align)\s*:\s*(left|right|center|justify)\s*$/i', $style, $match)
                    || preg_match('/^\s*(color|background-color)\s*:\s*(#[a-f0-9]{3,8}|[a-z]+|rgb\([\d ,]+\))\s*$/i', $style, $match)) {
                    $styles[] = $match[1].':'.$match[2];
                }
            }
            if ($styles) {
                $attributes .= ' style="'.htmlspecialchars(implode(';', $styles), ENT_QUOTES, 'UTF-8').'"';
            }
            $html .= '<'.$tag.$attributes.'>'.$content.(in_array($tag, ['br', 'hr'], true) ? '' : '</'.$tag.'>');
        }
        return $html;
    }
}
