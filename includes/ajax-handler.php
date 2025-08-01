<?php
if (! defined('ABSPATH')) exit;

// Hook untuk menyimpan template form
add_action('wp_ajax_mmbpg_save_settings', 'mmbpg_handle_save_settings');

// Hook untuk menyimpan opsi uninstall
add_action('wp_ajax_mmbpg_save_uninstall_setting', 'mmbpg_handle_save_uninstall_setting');

// Hook untuk proses generate post
add_action('wp_ajax_mmbpg_start_generation', 'mmbpg_handle_ajax_request');


function mmbpg_handle_save_settings()
{
    check_ajax_referer('mmbpg_ajax_nonce', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Anda tidak punya izin.']);
    }

    global $wpdb;
    $table_name = MMBPG_TABLE_NAME;
    $settings = isset($_POST['settings']) ? $_POST['settings'] : [];

    // Sanitasi data template
    $data = [
        'local_business_target' => sanitize_textarea_field($settings['local_business_target'] ?? ''),
        'post_title'            => sanitize_text_field($settings['post_title'] ?? ''),
        'post_content'          => wp_kses_post($settings['post_content'] ?? ''),
        'featured_images'       => sanitize_text_field($settings['featured_images'] ?? ''),
        'start_date'            => sanitize_text_field($settings['start_date'] ?? ''),
        'end_date'              => sanitize_text_field($settings['end_date'] ?? ''),
        'post_category'         => intval($settings['post_category'] ?? 0),
        'post_tags'             => sanitize_textarea_field($settings['post_tags'] ?? ''),
        'seo_lb_phone'          => sanitize_text_field($settings['seo_lb_phone'] ?? ''),
    ];

    $where = ['id' => 1];
    $result = $wpdb->update($table_name, $data, $where);

    if ($result === false) {
        wp_send_json_error(['message' => 'Gagal menyimpan template ke database.']);
    }

    wp_send_json_success();
}

function mmbpg_handle_save_uninstall_setting()
{
    check_ajax_referer('mmbpg_ajax_nonce', 'nonce');
    if (! current_user_can('manage_options')) {
        wp_send_json_error();
    }

    $value = sanitize_text_field($_POST['value']);
    update_option('mmbpg_erase_data_on_uninstall', $value === 'yes' ? 'yes' : 'no');
    wp_send_json_success();
}


function mmbpg_handle_ajax_request()
{
    check_ajax_referer('mmbpg_ajax_nonce', 'nonce');
    if (! current_user_can('publish_posts')) {
        wp_send_json_error(['message' => 'Anda tidak memiliki izin.']);
    }

    // Ambil data dari form
    $params = [
        'index'                 => isset($_POST['index']) ? intval($_POST['index']) : 0,
        'local_business_target' => isset($_POST['local_business_target']) ? sanitize_textarea_field($_POST['local_business_target']) : '',
        'post_title'            => isset($_POST['post_title']) ? sanitize_text_field($_POST['post_title']) : '',
        'post_content'          => isset($_POST['post_content']) ? wp_kses_post($_POST['post_content']) : '',
        'featured_images'       => isset($_POST['featured_images']) ? array_map('intval', explode(',', sanitize_text_field($_POST['featured_images']))) : [],
        'start_date'            => isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '',
        'end_date'              => isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '',
        'post_category'         => isset($_POST['post_category']) ? intval($_POST['post_category']) : 0,
        'post_tags'             => isset($_POST['post_tags']) ? sanitize_text_field($_POST['post_tags']) : '',
        'seo_lb_phone'          => isset($_POST['seo_lb_phone']) ? sanitize_text_field($_POST['seo_lb_phone']) : '',
    ];

    // Ambil setting default dari plugin Core
    $disable_comments = get_option('mmcbpg_disable_comments_default', true) ? 'closed' : 'open';
    $activate_schema_default = get_option('mmcbpg_activate_schema_default', true);

    $locations = array_filter(explode("\n", $params['local_business_target']));
    if (!isset($locations[$params['index']])) {
        wp_send_json_error(['message' => 'Index lokasi tidak valid.']);
    }

    $current_location_line = trim($locations[$params['index']]);
    $location_parts = array_map('trim', explode(',', $current_location_line));

    $kota      = isset($location_parts[0]) ? $location_parts[0] : '';
    $provinsi  = isset($location_parts[1]) ? $location_parts[1] : '';
    $kodepos   = isset($location_parts[2]) ? $location_parts[2] : '';

    $processed_title = mmbpg_spintax_process($params['post_title']);
    $processed_title = str_replace(['[kota]', '[provinsi]'], [$kota, $provinsi], $processed_title);
    $processed_content = mmbpg_spintax_process($params['post_content']);

    $random_post_date = mmbpg_get_random_date($params['start_date'], $params['end_date']);
    $random_image_id = !empty($params['featured_images']) ? $params['featured_images'][array_rand($params['featured_images'])] : 0;

    $alamat = sprintf('%s No.%d, %s, %s, %s, Indonesia', mmbpg_get_random_street(), rand(1, 300), $kota, $provinsi, $kodepos);
    $author_review_name = mmbpg_get_random_name();
    $author_rating      = mmbpg_get_random_float(4.5, 5.0);
    $total_review       = rand(2, 1500);
    $total_avg_rating   = mmbpg_get_random_float(4.5, 5.0);
    $pricerange         = '$-$$';

    sleep(3);

    $post_data = [
        'post_title'    => $processed_title,
        'post_content'  => $processed_content,
        'post_status'   => 'publish',
        'post_author'   => get_current_user_id(),
        'post_category' => [$params['post_category']],
        'post_date'     => $random_post_date,
        'post_date_gmt' => get_gmt_from_date($random_post_date),
        'comment_status' => $disable_comments,
        'ping_status'   => 'closed',
    ];

    $post_id = wp_insert_post($post_data, true);
    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => $post_id->get_error_message()]);
    }

    if (!empty($params['post_tags'])) {
        wp_set_post_tags($post_id, $params['post_tags'], true);
    }
    if ($random_image_id > 0) {
        set_post_thumbnail($post_id, $random_image_id);
    }

    // Simpan semua data ke post meta
    update_post_meta($post_id, 'seo_kota', $kota);
    update_post_meta($post_id, 'seo_provinsi', $provinsi);
    update_post_meta($post_id, 'seo_kodepos', $kodepos);
    update_post_meta($post_id, 'seo_lb_phone', $params['seo_lb_phone']);
    update_post_meta($post_id, 'seo_alamat', $alamat);
    update_post_meta($post_id, 'seo_author_review_name', $author_review_name);
    update_post_meta($post_id, 'seo_author_rating', $author_rating);
    update_post_meta($post_id, 'seo_total_review', $total_review);
    update_post_meta($post_id, 'seo_total_average_rating', $total_avg_rating);
    update_post_meta($post_id, 'pricerange', $pricerange);

    // Set meta untuk aktivasi schema berdasarkan setting default dari plugin Core
    if ($activate_schema_default) {
        update_post_meta($post_id, '_mmbpg_activate_schema', 'yes');
    }

    $message = sprintf(__('Post #%d "%s" berhasil dibuat untuk lokasi %s.', 'mm-bulk-post-generator'), $post_id, $processed_title, $kota);
    wp_send_json_success(['message' => $message]);
}
