<?php

/**
 * Plugin Name:       MM Bulk Post Generator
 * Plugin URI:        https://budiharyono.id/
 * Description:       A simple plugin to generate bulk posts with spintax, local SEO, and custom fields.
 * Version:           1.1.0
 * Author:            Budi Haryono
 * Author URI:        https://budiharyono.id/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mm-bulk-post-generator
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.0
 */

// Mencegah akses langsung ke file
if (! defined('ABSPATH')) {
    exit;
}

// Mendefinisikan konstanta untuk path, URL, dan nama tabel
define('MMBPG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MMBPG_PLUGIN_URL', plugin_dir_url(__FILE__));
global $wpdb;
define('MMBPG_TABLE_NAME', $wpdb->prefix . 'mmbpg_settings');

// Memuat file-file yang diperlukan
require_once MMBPG_PLUGIN_PATH . 'includes/database.php';
require_once MMBPG_PLUGIN_PATH . 'includes/spintax.php';
require_once MMBPG_PLUGIN_PATH . 'includes/helpers.php';
require_once MMBPG_PLUGIN_PATH . 'includes/ajax-handler.php';
require_once MMBPG_PLUGIN_PATH . 'includes/acf-integration.php';

// Menjalankan fungsi setup database saat plugin diaktifkan
register_activation_hook(__FILE__, 'mmbpg_activate');
function mmbpg_activate()
{
    mmbpg_create_database_table();
}

// Mendaftarkan hook untuk proses uninstalasi
register_uninstall_hook(__FILE__, 'mmbpg_uninstall');
// Fungsi uninstall ada di file uninstall.php untuk best practice

/**
 * Menambahkan halaman menu di dashboard admin.
 */
function mmbpg_add_admin_menu()
{
    add_menu_page(
        __('Bulk Post Generator', 'mm-bulk-post-generator'),
        'Bulk Post Gen',
        'manage_options',
        'mm-bulk-post-generator',
        'mmbpg_render_admin_page',
        'dashicons-admin-post',
        25
    );
}
add_action('admin_menu', 'mmbpg_add_admin_menu');

/**
 * Merender halaman admin plugin.
 */
function mmbpg_render_admin_page()
{
    // Memuat template halaman admin
    require_once MMBPG_PLUGIN_PATH . 'includes/admin-page.php';
}

/**
 * Mendaftarkan script dan style untuk halaman admin.
 */
function mmbpg_enqueue_admin_assets($hook)
{
    if ('toplevel_page_mm-bulk-post-generator' != $hook) {
        return;
    }

    // CSS
    wp_enqueue_style('mmbpg-admin-style', MMBPG_PLUGIN_URL . 'assets/css/admin-style.css', [], '1.1.0');

    // WordPress Media Uploader & Datepicker
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css');

    // JavaScript
    wp_enqueue_script('mmbpg-admin-script', MMBPG_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery', 'jquery-ui-datepicker'], '1.1.0', true);

    // Mengambil setting dari database untuk dikirim ke JavaScript
    global $wpdb;
    $settings = $wpdb->get_row("SELECT * FROM " . MMBPG_TABLE_NAME . " WHERE id = 1", ARRAY_A);
    if (is_null($settings)) {
        $settings = []; // Pastikan objek tidak null jika tabel kosong
    }

    // Mengirim data dari PHP ke JavaScript
    wp_localize_script('mmbpg-admin-script', 'mmbpg_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mmbpg_ajax_nonce'),
        'saved_settings' => $settings,
        'i18n' => [
            'process_complete' => __('Proses Selesai!', 'mm-bulk-post-generator'),
            'error_occurred' => __('Terjadi kesalahan. Silakan coba lagi.', 'mm-bulk-post-generator'),
            'starting_process' => __('Memulai proses...', 'mm-bulk-post-generator'),
            'settings_saved' => __('Pengaturan berhasil disimpan.', 'mm-bulk-post-generator'),
            'settings_reset' => __('Form telah direset. Tekan "Simpan Pengaturan" untuk menghapus data dari database.', 'mm-bulk-post-generator'),
            'confirm_reset' => __('Apakah Anda yakin ingin mereset semua field? Perubahan yang belum disimpan akan hilang.', 'mm-bulk-post-generator'),
        ]
    ]);
}
add_action('admin_enqueue_scripts', 'mmbpg_enqueue_admin_assets');
