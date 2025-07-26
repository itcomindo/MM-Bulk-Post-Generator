<?php

/**
 * Plugin Name:       MM Bulk Post Generator
 * Plugin URI:        https://budiharyono.id/
 * Description:       A simple plugin to generate bulk posts with spintax, local SEO, and custom fields.
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

// Mendefinisikan konstanta untuk path dan URL plugin
define('MMBPG_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MMBPG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Memuat file-file yang diperlukan
require_once MMBPG_PLUGIN_PATH . 'includes/spintax.php';
require_once MMBPG_PLUGIN_PATH . 'includes/helpers.php';
require_once MMBPG_PLUGIN_PATH . 'includes/ajax-handler.php';
require_once MMBPG_PLUGIN_PATH . 'includes/acf-integration.php';

/**
 * Menambahkan halaman menu di dashboard admin.
 */
function mmbpg_add_admin_menu()
{
    add_menu_page(
        __('Bulk Post Generator', 'mm-bulk-post-generator'), // Judul Halaman
        'Bulk Post Gen',                                       // Judul Menu
        'manage_options',                                      // Kapabilitas
        'mm-bulk-post-generator',                              // Menu Slug
        'mmbpg_render_admin_page',                             // Fungsi untuk render halaman
        'dashicons-admin-post',                                // Ikon
        25                                                     // Posisi
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
    // Hanya muat script di halaman plugin kita
    if ('toplevel_page_mm-bulk-post-generator' != $hook) {
        return;
    }

    // Mendaftarkan CSS
    wp_enqueue_style(
        'mmbpg-admin-style',
        MMBPG_PLUGIN_URL . 'assets/css/admin-style.css',
        [],
        '1.0.0'
    );

    // Mendaftarkan media uploader WordPress
    wp_enqueue_media();

    // Mendaftarkan jQuery UI Datepicker
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css');


    // Mendaftarkan JavaScript
    wp_enqueue_script(
        'mmbpg-admin-script',
        MMBPG_PLUGIN_URL . 'assets/js/admin-script.js',
        ['jquery', 'jquery-ui-datepicker'], // Bergantung pada jQuery
        '1.0.0',
        true // Muat di footer
    );

    // Mengirim data dari PHP ke JavaScript (untuk AJAX)
    wp_localize_script('mmbpg-admin-script', 'mmbpg_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('mmbpg_ajax_nonce'),
        'i18n' => [
            'process_complete' => __('Proses Selesai!', 'mm-bulk-post-generator'),
            'error_occurred' => __('Terjadi kesalahan. Silakan coba lagi.', 'mm-bulk-post-generator'),
            'starting_process' => __('Memulai proses...', 'mm-bulk-post-generator'),
        ]
    ]);
}
add_action('admin_enqueue_scripts', 'mmbpg_enqueue_admin_assets');
