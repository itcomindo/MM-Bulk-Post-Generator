<?php
if (! defined('ABSPATH')) exit;

/**
 * Menambahkan Meta Box ke halaman editor post.
 */
function mmbpg_add_schema_meta_box()
{
    add_meta_box(
        'mmbpg_schema_metabox',                  // ID Meta Box
        'MM SEO Local Business Schema',          // Judul Meta Box
        'mmbpg_render_schema_meta_box',          // Fungsi callback untuk render
        'post',                                  // Tipe post
        'side',                                  // Konteks (normal, side, advanced)
        'high'                                   // Prioritas
    );
}
add_action('add_meta_boxes', 'mmbpg_add_schema_meta_box');

/**
 * Merender konten Meta Box.
 * @param WP_Post $post Objek post saat ini.
 */
function mmbpg_render_schema_meta_box($post)
{
    // Menambahkan nonce field untuk verifikasi
    wp_nonce_field('mmbpg_save_schema_meta_box_data', 'mmbpg_schema_meta_box_nonce');

    // Mengambil nilai yang sudah ada dari database
    $value = get_post_meta($post->ID, '_mmbpg_activate_schema', true);

    // Menampilkan checkbox
    echo '<label for="mmbpg_activate_schema_field">';
    echo '<input type="checkbox" id="mmbpg_activate_schema_field" name="mmbpg_activate_schema_field" value="yes" ' . checked($value, 'yes', false) . ' />';
    _e(' Aktifkan Schema Markup', 'mm-bulk-post-generator');
    echo '</label>';
    echo '<p class="description">' . __('Jika dicentang, schema Local Business akan ditambahkan ke post ini.', 'mm-bulk-post-generator') . '</p>';
}

/**
 * Menyimpan data dari Meta Box saat post disimpan.
 * @param int $post_id ID dari post yang disimpan.
 */
function mmbpg_save_schema_meta_box_data($post_id)
{
    // 1. Verifikasi nonce
    if (! isset($_POST['mmbpg_schema_meta_box_nonce'])) {
        return;
    }
    if (! wp_verify_nonce($_POST['mmbpg_schema_meta_box_nonce'], 'mmbpg_save_schema_meta_box_data')) {
        return;
    }

    // 2. Hindari autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // 3. Cek izin pengguna
    if (isset($_POST['post_type']) && 'post' == $_POST['post_type']) {
        if (! current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    // 4. Update meta field di database.
    if (isset($_POST['mmbpg_activate_schema_field'])) {
        update_post_meta($post_id, '_mmbpg_activate_schema', 'yes');
    } else {
        delete_post_meta($post_id, '_mmbpg_activate_schema');
    }
}
add_action('save_post', 'mmbpg_save_schema_meta_box_data');


/**
 * Menyuntikkan JSON-LD Schema ke <head> jika diaktifkan.
 * Fungsi ini akan tetap berjalan selama ada post meta '_mmbpg_activate_schema'.
 */
function mmbpg_inject_schema_in_head()
{
    // Hanya berjalan di halaman single post
    if (! is_singular('post')) {
        return;
    }

    $post_id = get_the_ID();
    $activate_schema = get_post_meta($post_id, '_mmbpg_activate_schema', true);

    // Jika tidak diaktifkan, berhenti
    if ('yes' !== $activate_schema) {
        return;
    }

    // Kumpulkan semua data yang dibutuhkan dari custom fields
    $nama_bisnis = get_the_title($post_id);
    $alamat_jalan = get_post_meta($post_id, 'seo_alamat', true);
    $kota = get_post_meta($post_id, 'seo_kota', true);
    $provinsi = get_post_meta($post_id, 'seo_provinsi', true);
    $kodepos = get_post_meta($post_id, 'seo_kodepos', true);
    $telepon = get_post_meta($post_id, 'seo_lb_phone', true);
    $rating_value = get_post_meta($post_id, 'seo_total_average_rating', true);
    $review_count = get_post_meta($post_id, 'seo_total_review', true);
    $price_range = get_post_meta($post_id, 'pricerange', true);
    $author_name = get_post_meta($post_id, 'seo_author_review_name', true);
    $author_rating = get_post_meta($post_id, 'seo_author_rating', true);

    // Bangun struktur schema
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $nama_bisnis,
        'image' => get_the_post_thumbnail_url($post_id, 'full'),
        'url' => get_permalink($post_id),
        'telephone' => $telepon,
        'priceRange' => $price_range,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $alamat_jalan,
            'addressLocality' => $kota,
            'addressRegion' => $provinsi,
            'postalCode' => $kodepos,
            'addressCountry' => 'ID'
        ],
        'aggregateRating' => [
            '@type' => 'AggregateRating',
            'ratingValue' => $rating_value,
            'reviewCount' => $review_count
        ],
        'review' => [
            '@type' => 'Review',
            'author' => [
                '@type' => 'Person',
                'name' => $author_name
            ],
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $author_rating,
                'bestRating' => '5'
            ],
            'reviewBody' => 'Layanan yang sangat memuaskan dan profesional. Sangat direkomendasikan!' // Contoh review body
        ]
    ];

    // Tampilkan schema dalam format JSON-LD
    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
}
add_action('wp_head', 'mmbpg_inject_schema_in_head');
