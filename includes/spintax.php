<?php
if (! defined('ABSPATH')) exit;

/**
 * Memproses string dengan format spintax.
 * Mendukung nested spintax.
 * Contoh: {Hello|Hi} {{World|Universe}|Everyone}!
 */
function mmbpg_spintax_process($text)
{
    $pattern = '/\{([^{}]*)\}/';
    while (preg_match($pattern, $text, $matches)) {
        $parts = explode('|', $matches[1]);
        $text = substr_replace(
            $text,
            $parts[array_rand($parts)],
            strpos($text, $matches[0]),
            strlen($matches[0])
        );
    }
    return $text;
}
