<?php
// Jika uninstall tidak dipanggil dari WordPress, keluar
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Periksa apakah pengguna memilih untuk menghapus data
$erase_on_uninstall = get_option('mmbpg_erase_data_on_uninstall');

if ($erase_on_uninstall === 'yes') {
    global $wpdb;

    // Definisikan nama tabel
    $table_name = $wpdb->prefix . 'mmbpg_settings';

    // Hapus tabel kustom untuk menyimpan template
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    // Hapus opsi dari tabel wp_options
    delete_option('mmbpg_erase_data_on_uninstall');
}
