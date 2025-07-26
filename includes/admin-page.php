<?php

/**
 *
 * Silence is golden
 */

defined('ABSPATH') || die('No script kiddies please!');
?>
<div class="wrap mmbpg-wrapper">
    <h1><?php _e('MM Bulk Post Generator', 'mm-bulk-post-generator'); ?></h1>
    <p><?php _e('Gunakan form di bawah ini untuk membuat post secara massal dengan data Local Business.', 'mm-bulk-post-generator'); ?></p>

    <form id="mmbpg-form">
        <div id="mmbpg-notice" class="notice" style="display:none;"></div>

        <div class="mmbpg-progress-bar-container" style="display:none;">
            <div class="mmbpg-progress-bar"></div>
            <div class="mmbpg-progress-status"></div>
        </div>

        <table class="form-table">
            <!-- 1. Local Business Target -->
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_local_business_target"><?php _e('Local Business Target', 'mm-bulk-post-generator'); ?></label>
                    <span class="required">*</span>
                </th>
                <td>
                    <textarea id="mmbpg_local_business_target" name="mmbpg_local_business_target" class="large-text required-field" rows="10" placeholder="Contoh:
Bandung,Jawa Barat,45457
Kota Tangerang,Banten,15156"></textarea>
                    <p class="description"><?php _e('Masukan data dengan format: [Kota],[Provinsi],[KodePos]. Pisahkan setiap lokasi dengan baris baru (Enter).', 'mm-bulk-post-generator'); ?></p>
                </td>
            </tr>

            <!-- 2. Post Title -->
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_post_title"><?php _e('Judul Post', 'mm-bulk-post-generator'); ?></label>
                    <span class="required">*</span>
                </th>
                <td>
                    <input type="text" id="mmbpg_post_title" name="mmbpg_post_title" class="large-text required-field" placeholder="{Jasa|Layanan} Pembuatan Website di [kota] [provinsi]">
                    <p class="description"><?php _e('Gunakan format spintax. Gunakan placeholder [kota] dan [provinsi].', 'mm-bulk-post-generator'); ?></p>
                </td>
            </tr>

            <!-- 3. Post Content -->
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_post_content"><?php _e('Artikel', 'mm-bulk-post-generator'); ?></label>
                    <span class="required">*</span>
                </th>
                <td>
                    <?php
                    $content = '';
                    $editor_id = 'mmbpg_post_content';
                    $settings = [
                        'textarea_name' => 'mmbpg_post_content',
                        'media_buttons' => true,
                        'textarea_rows' => 15,
                        'tinymce'       => true,
                    ];
                    wp_editor($content, $editor_id, $settings);
                    ?>
                    <p class="description"><?php _e('Gunakan format spintax di dalam konten.', 'mm-bulk-post-generator'); ?></p>
                </td>
            </tr>

            <!-- 4. Featured Images -->
            <tr valign="top">
                <th scope="row">
                    <label><?php _e('Featured Images', 'mm-bulk-post-generator'); ?></label>
                    <span class="required">*</span>
                </th>
                <td>
                    <div class="mmbpg-image-gallery"></div>
                    <input type="hidden" id="mmbpg_featured_images" name="mmbpg_featured_images" class="required-field">
                    <button type="button" class="button" id="mmbpg-upload-image-button"><?php _e('Pilih Gambar', 'mm-bulk-post-generator'); ?></button>
                    <p class="description"><?php _e('Pilih satu atau lebih gambar. Satu gambar akan dipilih secara acak untuk setiap post.', 'mm-bulk-post-generator'); ?></p>
                </td>
            </tr>

            <!-- 5. Post Published Date -->
            <tr valign="top">
                <th scope="row"><?php _e('Jadwalkan Post', 'mm-bulk-post-generator'); ?><span class="required">*</span></th>
                <td>
                    <label for="mmbpg_start_date"><?php _e('Mulai dari:', 'mm-bulk-post-generator'); ?></label>
                    <input type="text" id="mmbpg_start_date" name="mmbpg_start_date" class="mmbpg-datepicker required-field" autocomplete="off">

                    <label for="mmbpg_end_date" style="margin-left: 20px;"><?php _e('Sampai dengan:', 'mm-bulk-post-generator'); ?></label>
                    <input type="text" id="mmbpg_end_date" name="mmbpg_end_date" class="mmbpg-datepicker required-field" autocomplete="off">
                    <p class="description"><?php _e('Post akan dipublikasikan secara acak di antara rentang tanggal ini.', 'mm-bulk-post-generator'); ?></p>
                </td>
            </tr>

            <!-- 6. Category -->
            <tr valign="top">
                <th scope="row"><?php _e('Kategori Post', 'mm-bulk-post-generator'); ?><span class="required">*</span></th>
                <td>
                    <?php
                    $categories = get_categories(['hide_empty' => 0]);
                    if (! empty($categories)) {
                        echo '<fieldset id="mmbpg_post_category" class="required-field-radio">';
                        foreach ($categories as $category) {
                            printf(
                                '<label><input type="radio" name="mmbpg_post_category" value="%d"> %s</label><br>',
                                esc_attr($category->term_id),
                                esc_html($category->name)
                            );
                        }
                        echo '</fieldset>';
                    } else {
                        _e('Tidak ada kategori ditemukan. Silakan buat satu terlebih dahulu.', 'mm-bulk-post-generator');
                    }
                    ?>
                </td>
            </tr>

            <!-- 7. Tags -->
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_post_tags"><?php _e('Tag Post', 'mm-bulk-post-generator'); ?></label>
                </th>
                <td>
                    <textarea id="mmbpg_post_tags" name="mmbpg_post_tags" class="large-text" rows="3" placeholder="Jasa, Layanan, Web Developer"></textarea>
                    <p class="description"><?php _e('Pisahkan setiap tag dengan koma.', 'mm-bulk-post-generator'); ?></p>
                </td>
            </tr>

            <!-- 8. Nomor Telepon -->
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_seo_lb_phone"><?php _e('Nomor Telepon', 'mm-bulk-post-generator'); ?></label>
                </th>
                <td>
                    <input type="text" id="mmbpg_seo_lb_phone" name="mmbpg_seo_lb_phone" class="regular-text" value="0822-3356-6320">
                </td>
            </tr>

            <!-- 9. Disable Comments -->
            <tr valign="top">
                <th scope="row"><?php _e('Opsi Komentar', 'mm-bulk-post-generator'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="mmbpg_disable_comments" value="1" checked>
                        <?php _e('Nonaktifkan komentar untuk semua post yang dibuat', 'mm-bulk-post-generator'); ?>
                    </label>
                </td>
            </tr>

        </table>

        <?php submit_button(__('START GENERATE', 'mm-bulk-post-generator'), 'primary', 'mmbpg-start-button', true, ['disabled' => 'disabled']); ?>
    </form>
</div>