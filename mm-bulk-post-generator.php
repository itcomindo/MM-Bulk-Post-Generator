<?php

/**
 * Plugin Name:       MM Bulk Post Generator
 * Plugin URI:        https://budiharyono.id/
 * Description:       The engine plugin for generating bulk posts. Requires MM CORE Bulk Post Generator.
 * Version:           1.0.0
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

// Mendefinisikan konstanta
define('MMBPG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MMBPG_PLUGIN_URL', plugin_dir_url(__FILE__));
global $wpdb;
define('MMBPG_TABLE_NAME', $wpdb->prefix . 'mmbpg_settings');

/**
 * Fungsi utama untuk memeriksa dependensi dan memuat plugin.
 */
function mmbpg_load_plugin()
{
    // Periksa apakah plugin Core aktif
    if (! function_exists('mmcbpg_core_loaded')) {
        add_action('admin_notices', 'mmbpg_dependency_notice');
        return; // Hentikan pemuatan sisa plugin
    }

    // Jika dependensi terpenuhi, muat sisa file plugin
    mmbpg_include_files();
    mmbpg_add_hooks();
}
add_action('plugins_loaded', 'mmbpg_load_plugin');

/**
 * Menampilkan notifikasi admin jika plugin Core tidak aktif.
 */
function mmbpg_dependency_notice()
{
?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php
            _e('<strong>MM Bulk Post Generator</strong> requires the <strong>MM CORE Bulk Post Generator</strong> plugin to be installed and activated. Please activate the Core plugin.', 'mm-bulk-post-generator');
            ?>
        </p>
    </div>
<?php
    // Menonaktifkan plugin ini secara otomatis untuk mencegah error
    deactivate_plugins(plugin_basename(__FILE__));
}

/**
 * Memuat file-file yang diperlukan untuk plugin generator.
 */
function mmbpg_include_files()
{
    require_once MMBPG_PLUGIN_PATH . 'includes/database.php';
    require_once MMBPG_PLUGIN_PATH . 'includes/spintax.php';
    require_once MMBPG_PLUGIN_PATH . 'includes/helpers.php';
    require_once MMBPG_PLUGIN_PATH . 'includes/ajax-handler.php';
}

/**
 * Menambahkan semua hook yang relevan untuk plugin generator.
 */
function mmbpg_add_hooks()
{
    register_activation_hook(__FILE__, 'mmbpg_activate');
    register_uninstall_hook(__FILE__, 'mmbpg_uninstall');
    add_action('admin_menu', 'mmbpg_add_admin_menu');
    add_action('admin_enqueue_scripts', 'mmbpg_enqueue_admin_assets');
}

/**
 * Fungsi yang dijalankan saat aktivasi.
 */
function mmbpg_activate()
{
    // Fungsi ini akan memeriksa dependensi saat diaktifkan
    if (! function_exists('mmcbpg_core_loaded')) {
        // Tampilkan error dan cegah aktivasi jika bisa
        wp_die(
            __('Please install and activate the <strong>MM CORE Bulk Post Generator</strong> plugin first.', 'mm-bulk-post-generator'),
            __('Plugin Dependency Error', 'mm-bulk-post-generator'),
            ['back_link' => true]
        );
    }
    // Jika Core aktif, buat tabel
    mmbpg_create_database_table();
}

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
    if ('toplevel_page_mm-bulk-post-generator' != $hook) {
        return;
    }

    wp_enqueue_style('mmbpg-admin-style', MMBPG_PLUGIN_URL . 'assets/css/admin-style.css', [], '1.0.0');
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('mmbpg-admin-script', MMBPG_PLUGIN_URL . 'assets/js/admin-script.js', ['jquery', 'jquery-ui-datepicker'], '1.0.0', true);

    // Mengambil data dari PHP untuk dikirim ke JavaScript
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
