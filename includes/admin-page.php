<?php
// Mengambil data pengaturan yang tersimpan
global $wpdb;
$settings = $wpdb->get_row("SELECT * FROM " . MMBPG_TABLE_NAME . " WHERE id = 1", ARRAY_A);
if (!$settings) $settings = []; // hindari error jika null

// Menetapkan nilai default
$activate_schema_default = isset($settings['activate_schema_default']) ? (bool)$settings['activate_schema_default'] : true;
$disable_comments = isset($settings['disable_comments']) ? (bool)$settings['disable_comments'] : true;
$erase_on_uninstall = get_option('mmbpg_erase_data_on_uninstall', 'no');

$local_business_target = $settings['local_business_target'] ?? '';
$post_title = $settings['post_title'] ?? '';
$post_content = $settings['post_content'] ?? '';
$featured_images = $settings['featured_images'] ?? '';
$start_date = $settings['start_date'] ?? '';
$end_date = $settings['end_date'] ?? '';
$selected_category = $settings['post_category'] ?? 0;
$post_tags = $settings['post_tags'] ?? '';
$seo_lb_phone = $settings['seo_lb_phone'] ?? '0822-3356-6320';
?>
<div class="wrap mmbpg-wrapper">
    <h1>
        <?php _e('MM Bulk Post Generator', 'mm-bulk-post-generator'); ?>
        <span class="author-credit">
            <?php printf(
                __('Plugin dibuat oleh %s', 'mm-bulk-post-generator'),
                '<a href="https://budiharyono.id/" target="_blank">Budi Haryono</a>'
            ); ?>
        </span>
    </h1>

    <form id="mmbpg-form">
        <div id="mmbpg-notice" class="notice" style="display:none;"></div>

        <!-- PENGATURAN GLOBAL DIPINDAHKAN KE ATAS -->
        <div class="mmbpg-global-settings">
            <h2><?php _e('Pengaturan Global', 'mm-bulk-post-generator'); ?></h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Opsi Default', 'mm-bulk-post-generator'); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="checkbox" name="activate_schema_default" value="1" <?php checked($activate_schema_default); ?>> <?php _e('Aktifkan Schema Local Business secara default untuk post baru', 'mm-bulk-post-generator'); ?></label><br>
                            <label><input type="checkbox" name="disable_comments" value="1" <?php checked($disable_comments); ?>> <?php _e('Nonaktifkan komentar secara default untuk post baru', 'mm-bulk-post-generator'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Pengaturan Plugin', 'mm-bulk-post-generator'); ?></th>
                    <td>
                        <label><input type="checkbox" id="mmbpg_erase_data_on_uninstall" name="erase_on_uninstall" value="yes" <?php checked($erase_on_uninstall, 'yes'); ?>> <?php _e('Hapus semua data plugin ini saat uninstall.', 'mm-bulk-post-generator'); ?></label>
                        <p class="description"><?php _e('Peringatan: Aksi ini akan menghapus tabel template Anda secara permanen.', 'mm-bulk-post-generator'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <h2><?php _e('Template Generator Post', 'mm-bulk-post-generator'); ?></h2>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_local_business_target"><?php _e('Local Business Target', 'mm-bulk-post-generator'); ?></label><span class="required">*</span>
                </th>
                <td>
                    <textarea id="mmbpg_local_business_target" name="local_business_target" class="large-text required-field" rows="10" placeholder="Contoh:
Bandung,Jawa Barat,45457"><?php echo esc_textarea($local_business_target); ?></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_post_title"><?php _e('Judul Post', 'mm-bulk-post-generator'); ?></label><span class="required">*</span>
                </th>
                <td>
                    <input type="text" id="mmbpg_post_title" name="post_title" class="large-text required-field" value="<?php echo esc_attr($post_title); ?>" placeholder="{Jasa|Layanan} di [kota]">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="mmbpg_post_content"><?php _e('Artikel', 'mm-bulk-post-generator'); ?></label><span class="required">*</span>
                </th>
                <td>
                    <?php wp_editor($post_content, 'mmbpg_post_content', ['textarea_name' => 'post_content', 'textarea_rows' => 15, 'tinymce' => true]); ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label><?php _e('Featured Images', 'mm-bulk-post-generator'); ?></label><span class="required">*</span>
                </th>
                <td>
                    <div class="mmbpg-image-gallery">
                        <?php
                        if (!empty($featured_images)) {
                            $image_ids = explode(',', $featured_images);
                            foreach ($image_ids as $image_id) {
                                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                if ($image_url) {
                                    echo '<div class="image-container" data-id="' . esc_attr($image_id) . '"><img src="' . esc_url($image_url) . '" /><span class="remove-image">x</span></div>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <input type="hidden" id="mmbpg_featured_images" name="featured_images" class="required-field" value="<?php echo esc_attr($featured_images); ?>">
                    <button type="button" class="button" id="mmbpg-upload-image-button"><?php _e('Pilih Gambar', 'mm-bulk-post-generator'); ?></button>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Jadwalkan Post', 'mm-bulk-post-generator'); ?><span class="required">*</span></th>
                <td>
                    <label for="mmbpg_start_date"><?php _e('Mulai dari:', 'mm-bulk-post-generator'); ?></label>
                    <input type="text" id="mmbpg_start_date" name="start_date" class="mmbpg-datepicker required-field" autocomplete="off" value="<?php echo esc_attr($start_date); ?>">
                    <label for="mmbpg_end_date"><?php _e('Sampai dengan:', 'mm-bulk-post-generator'); ?></label>
                    <input type="text" id="mmbpg_end_date" name="end_date" class="mmbpg-datepicker required-field" autocomplete="off" value="<?php echo esc_attr($end_date); ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php _e('Kategori Post', 'mm-bulk-post-generator'); ?><span class="required">*</span></th>
                <td>
                    <fieldset id="mmbpg_post_category" class="required-field-radio">
                        <?php
                        $categories = get_categories(['hide_empty' => 0]);
                        if (!empty($categories)) {
                            foreach ($categories as $category) {
                                printf('<label><input type="radio" name="post_category" value="%d" %s> %s</label><br>', esc_attr($category->term_id), checked($selected_category, $category->term_id, false), esc_html($category->name));
                            }
                        } else {
                            _e('Tidak ada kategori. Buat satu terlebih dahulu.', 'mm-bulk-post-generator');
                        }
                        ?>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmbpg_post_tags"><?php _e('Tag Post', 'mm-bulk-post-generator'); ?></label></th>
                <td>
                    <textarea id="mmbpg_post_tags" name="post_tags" class="large-text" rows="3" placeholder="Jasa, Layanan"><?php echo esc_textarea($post_tags); ?></textarea>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmbpg_seo_lb_phone"><?php _e('Nomor Telepon', 'mm-bulk-post-generator'); ?></label></th>
                <td>
                    <input type="text" id="mmbpg_seo_lb_phone" name="seo_lb_phone" class="regular-text" value="<?php echo esc_attr($seo_lb_phone); ?>">
                </td>
            </tr>
        </table>

        <!-- Tombol Aksi -->
        <div class="mmbpg-actions">
            <button id="mmbpg-start-button" class="button button-primary" disabled><?php _e('START GENERATE', 'mm-bulk-post-generator'); ?></button>
            <button type="button" id="mmbpg-save-button" class="button button-save"><?php _e('Simpan Pengaturan', 'mm-bulk-post-generator'); ?></button>
            <button type="button" id="mmbpg-reset-button" class="button button-reset"><?php _e('Reset Form', 'mm-bulk-post-generator'); ?></button>
            <button type="button" id="mmbpg-undo-reset-button" class="button button-secondary" style="display:none;"><?php _e('Undo Reset', 'mm-bulk-post-generator'); ?></button>
        </div>

        <!-- Progress Bar -->
        <div class="mmbpg-progress-bar-container" style="display:none;">
            <div class="mmbpg-progress-bar"></div>
            <div class="mmbpg-progress-status"></div>
        </div>
    </form>
</div>