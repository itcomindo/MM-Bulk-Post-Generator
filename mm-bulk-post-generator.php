<?php

/**
 * Plugin Name:       MM Bulk Post Generator
 * Plugin URI:        https://budiharyono.id/
 * Description:       A simple plugin to generate bulk posts with spintax, local SEO, shortcodes, and schema markup.
 * Version:           1.0.0
 * Author:            Budi Haryono
 * Author URI:        https://budiharyono.id/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mm-bulk-post-generator
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * require:   mm-core-bulk-post-generator
 * 
 */



// Mencegah akses langsung ke file
if (! defined('ABSPATH')) {
    exit;
}


// ======================================================================
// KONFIGURASI UPDATER - INI SATU-SATUNYA BAGIAN YANG PERLU DIUBAH
// ======================================================================

// Ganti nilai-nilai di bawah ini sesuai dengan plugin baru Anda.
$my_plugin_slug    = 'mm-bulk-post-generator'; // Harus sama dengan slug folder/file plugin.
$my_plugin_api_url = 'https://plugins.budiharyono.com/' . $my_plugin_slug . '/info.json';

// ======================================================================
// KODE PEMANGGIL UPDATER - JANGAN UBAH BAGIAN DI BAWAH INI
// ======================================================================

// 1. Muat pustaka updater.
require_once __DIR__ . '/lib/updater.php';

// 2. Daftarkan hook aktivasi yang memanggil metode statis dari pustaka.
//    Ini mencegah konflik nama fungsi.
register_activation_hook(__FILE__, ['My_Plugin_Updater_Library', 'on_activation']);

// 3. Inisialisasi updater jika di area admin.
if (is_admin()) {
    new My_Plugin_Updater_Library(__FILE__, $my_plugin_slug, $my_plugin_api_url);
}

// ----------------------------------------------------------------------
//
// Tempatkan kode fungsional plugin Anda di sini...
//
// ----------------------------------------------------------------------






// Mendefinisikan konstanta
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
require_once MMBPG_PLUGIN_PATH . 'includes/shortcodes.php';
require_once MMBPG_PLUGIN_PATH . 'includes/schema-generator.php';

// Menjalankan fungsi setup database saat plugin diaktifkan
register_activation_hook(__FILE__, 'mmbpg_activate');
function mmbpg_activate()
{
    mmbpg_create_database_table();
}

// Mendaftarkan hook untuk proses uninstalasi
register_uninstall_hook(__FILE__, 'mmbpg_uninstall');


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
    require_once MMBPG_PLUGIN_PATH . 'includes/admin-page.php';
}

/**
 * Mendaftarkan script dan style untuk halaman admin.
 */
function mmbpg_enqueue_admin_assets($hook)
{
    // Hanya muat di halaman admin plugin kita
    if ('toplevel_page_mm-bulk-post-generator' != $hook) {
        return;
    }

    wp_enqueue_style('mmbpg-admin-style', MMBPG_PLUGIN_URL . 'assets/css/admin-style.css', [], '1.2.0');
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('mmbpg-admin-script', MMBPG_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery', 'jquery-ui-datepicker'], '1.2.0', true);

    // Mengirim data dari PHP ke JavaScript
    global $wpdb;
    $settings = $wpdb->get_row("SELECT * FROM " . MMBPG_TABLE_NAME . " WHERE id = 1", ARRAY_A);
    if (is_null($settings)) {
        $settings = [];
    }
    $settings['erase_on_uninstall'] = get_option('mmbpg_erase_data_on_uninstall', 'no');


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
