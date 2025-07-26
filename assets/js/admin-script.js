jQuery(document).ready(function ($) {

    // Fungsi untuk memeriksa field yang wajib diisi
    function checkRequiredFields() {
        let allFilled = true;
        $('.required-field').each(function () {
            if ($(this).val() === '' || $(this).val() === null) {
                allFilled = false;
            }
        });

        // Khusus untuk editor TinyMCE
        if (typeof tinymce !== 'undefined' && tinymce.get('mmbpg_post_content') && tinymce.get('mmbpg_post_content').getContent() === '') {
            allFilled = false;
        }

        // Khusus untuk radio button kategori
        if ($('.required-field-radio').length > 0 && $('input[name="mmbpg_post_category"]:checked').length === 0) {
            allFilled = false;
        }

        if (allFilled) {
            $('#mmbpg-start-button').prop('disabled', false);
        } else {
            $('#mmbpg-start-button').prop('disabled', true);
        }
    }

    // Panggil fungsi check saat ada perubahan pada form
    $('.required-field, .required-field-radio input').on('keyup change', checkRequiredFields);
    if (typeof tinymce !== 'undefined') {
        tinymce.on('addeditor', function (e) {
            if (e.editor.id === 'mmbpg_post_content') {
                e.editor.on('keyup change', checkRequiredFields);
            }
        });
    }

    // Inisialisasi Datepicker
    $('.mmbpg-datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        onSelect: function () {
            checkRequiredFields();
        }
    });

    // Inisialisasi pengecekan awal
    checkRequiredFields();


    // Logika Media Uploader untuk Featured Image
    let mediaUploader;
    $('#mmbpg-upload-image-button').on('click', function (e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Pilih Gambar untuk Featured Image',
            button: {
                text: 'Gunakan Gambar Ini'
            },
            multiple: true // Izinkan multi-select
        });

        mediaUploader.on('select', function () {
            const attachments = mediaUploader.state().get('selection').toJSON();
            let image_ids = $('#mmbpg_featured_images').val() ? $('#mmbpg_featured_images').val().split(',') : [];

            attachments.forEach(function (attachment) {
                if (!image_ids.includes(attachment.id.toString())) {
                    image_ids.push(attachment.id.toString());
                    $('.mmbpg-image-gallery').append(
                        `<div class="image-container" data-id="${attachment.id}">
                            <img src="${attachment.sizes.thumbnail.url}" />
                            <span class="remove-image">x</span>
                        </div>`
                    );
                }
            });

            $('#mmbpg_featured_images').val(image_ids.join(',')).trigger('change');
        });

        mediaUploader.open();
    });

    // Hapus gambar dari pilihan
    $('.mmbpg-image-gallery').on('click', '.remove-image', function () {
        const container = $(this).closest('.image-container');
        const idToRemove = container.data('id').toString();
        let image_ids = $('#mmbpg_featured_images').val().split(',');

        image_ids = image_ids.filter(id => id !== idToRemove);

        $('#mmbpg_featured_images').val(image_ids.join(',')).trigger('change');
        container.remove();
    });


    // Proses utama saat tombol START ditekan
    $('#mmbpg-form').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);
        const startButton = $('#mmbpg-start-button');
        const progressBarContainer = $('.mmbpg-progress-bar-container');
        const progressBar = $('.mmbpg-progress-bar');
        const progressStatus = $('.mmbpg-progress-status');
        const noticeDiv = $('#mmbpg-notice');

        // Update UI untuk memulai proses
        form.addClass('processing');
        startButton.prop('disabled', true);
        progressBarContainer.show();
        progressBar.css('width', '0%').text('0%');
        progressStatus.text(mmbpg_ajax_obj.i18n.starting_process);
        noticeDiv.hide().removeClass('notice-success notice-error');

        // Ambil data dari form
        const formData = {
            action: 'mmbpg_start_generation',
            nonce: mmbpg_ajax_obj.nonce,
            local_business_target: $('#mmbpg_local_business_target').val(),
            post_title: $('#mmbpg_post_title').val(),
            post_content: tinymce.get('mmbpg_post_content').getContent(),
            featured_images: $('#mmbpg_featured_images').val(),
            start_date: $('#mmbpg_start_date').val(),
            end_date: $('#mmbpg_end_date').val(),
            post_category: $('input[name="mmbpg_post_category"]:checked').val(),
            post_tags: $('#mmbpg_post_tags').val(),
            seo_lb_phone: $('#mmbpg_seo_lb_phone').val(),
            disable_comments: $('input[name="mmbpg_disable_comments"]:checked').val() || '0'
        };

        const locations = formData.local_business_target.split('\n').filter(line => line.trim() !== '');
        const total_posts = locations.length;
        let posts_processed = 0;

        // Fungsi rekursif untuk memproses setiap post satu per satu
        function processPost(index) {
            if (index >= total_posts) {
                // Selesai
                form.removeClass('processing');
                checkRequiredFields(); // Re-enable start button if form is still valid
                progressStatus.text(mmbpg_ajax_obj.i18n.process_complete);
                noticeDiv.text(mmbpg_ajax_obj.i18n.process_complete).addClass('notice-success').show();
                return;
            }

            const ajaxData = { ...formData, index: index };

            $.post(mmbpg_ajax_obj.ajax_url, ajaxData, function (response) {
                if (response.success) {
                    posts_processed++;
                    const progress = (posts_processed / total_posts) * 100;

                    progressBar.css('width', progress + '%').text(Math.round(progress) + '%');
                    progressStatus.html(response.data.message);

                    // Panggil untuk item berikutnya
                    processPost(index + 1);
                } else {
                    // Terjadi error
                    form.removeClass('processing');
                    checkRequiredFields();
                    progressBarContainer.hide();
                    progressStatus.text('');
                    noticeDiv.text(mmbpg_ajax_obj.i18n.error_occurred + ': ' + response.data.message).addClass('notice-error').show();
                }
            }).fail(function () {
                // Error koneksi AJAX
                form.removeClass('processing');
                checkRequiredFields();
                progressBarContainer.hide();
                progressStatus.text('');
                noticeDiv.text(mmbpg_ajax_obj.i18n.error_occurred).addClass('notice-error').show();
            });
        }

        // Mulai proses dari index 0
        processPost(0);
    });
});