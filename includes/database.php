<?php
if (! defined('ABSPATH')) exit;

/**
 * Membuat tabel kustom di database saat aktivasi plugin.
 */
function mmbpg_create_database_table()
{
    global $wpdb;
    $table_name = MMBPG_TABLE_NAME;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        local_business_target longtext NOT NULL,
        post_title text NOT NULL,
        post_content longtext NOT NULL,
        featured_images text NOT NULL,
        start_date varchar(20) NOT NULL,
        end_date varchar(20) NOT NULL,
        post_category int(11) NOT NULL,
        post_tags text NOT NULL,
        seo_lb_phone varchar(50) NOT NULL,
        disable_comments tinyint(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Inisialisasi baris pertama jika belum ada
    $row_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE id = 1");
    if (!$row_exists) {
        $wpdb->insert(
            $table_name,
            [
                'id' => 1,
                'local_business_target' => '',
                'post_title' => '',
                'post_content' => '',
                'featured_images' => '',
                'start_date' => '',
                'end_date' => '',
                'post_category' => 0,
                'post_tags' => '',
                'seo_lb_phone' => '0822-3356-6320',
                'disable_comments' => 1,
            ]
        );
    }
}
