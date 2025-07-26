<?php
if (! defined('ABSPATH')) exit;

/**
 * Fungsi dasar untuk mengambil post meta untuk shortcode.
 * @param string $meta_key Key dari custom field.
 * @param array $atts Atribut shortcode (tidak digunakan saat ini).
 * @return string Nilai dari meta field.
 */
function mmbpg_get_meta_shortcode($meta_key, $atts = [])
{
    $post_id = get_the_ID();
    if (! $post_id) {
        return '';
    }
    $value = get_post_meta($post_id, $meta_key, true);
    return esc_html($value);
}

// Shortcode untuk Judul Post
add_shortcode('judul_post', function ($atts) {
    $post_id = get_the_ID();
    return $post_id ? esc_html(get_the_title($post_id)) : '';
});

// Shortcode untuk data SEO Lokal
add_shortcode('kota', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_kota', $atts);
});
add_shortcode('provinsi', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_provinsi', $atts);
});
add_shortcode('kodepos', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_kodepos', $atts);
});
add_shortcode('nomor_telepon', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_lb_phone', $atts);
});
add_shortcode('alamat', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_alamat', $atts);
});

// Shortcode untuk data Review
add_shortcode('author_review_name', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_author_review_name', $atts);
});
add_shortcode('author_rating', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_author_rating', $atts);
});
add_shortcode('total_review', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_total_review', $atts);
});
add_shortcode('total_average_rating', function ($atts) {
    return mmbpg_get_meta_shortcode('seo_total_average_rating', $atts);
});
add_shortcode('pricerange', function ($atts) {
    return mmbpg_get_meta_shortcode('pricerange', $atts);
});
