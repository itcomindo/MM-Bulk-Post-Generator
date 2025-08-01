jQuery(document).ready(function ($) {
    let undoState = {};

    function showNotice(message, type = 'success') {
        const noticeDiv = $('#mmbpg-notice');
        noticeDiv.removeClass('notice-success notice-error notice-warning notice-info').addClass('notice-' + type);
        noticeDiv.text(message).slideDown();
        setTimeout(() => noticeDiv.slideUp(), 5000);
    }

    function getFormData() {
        return {
            local_business_target: $('#mmbpg_local_business_target').val(),
            post_title: $('#mmbpg_post_title').val(),
            post_content: (typeof tinymce !== 'undefined' && tinymce.get('mmbpg_post_content')) ? tinymce.get('mmbpg_post_content').getContent() : $('#mmbpg_post_content').val(),
            featured_images: $('#mmbpg_featured_images').val(),
            start_date: $('#mmbpg_start_date').val(),
            end_date: $('#mmbpg_end_date').val(),
            post_category: $('input[name="post_category"]:checked').val() || 0,
            post_tags: $('#mmbpg_post_tags').val(),
            seo_lb_phone: $('#mmbpg_seo_lb_phone').val(),
        };
    }

    function populateForm(data) {
        $('#mmbpg_local_business_target').val(data.local_business_target || '');
        $('#mmbpg_post_title').val(data.post_title || '');
        if (typeof tinymce !== 'undefined' && tinymce.get('mmbpg_post_content')) {
            tinymce.get('mmbpg_post_content').setContent(data.post_content || '');
        } else {
            $('#mmbpg_post_content').val(data.post_content || '');
        }
        $('#mmbpg_featured_images').val(data.featured_images || '');
        $('#mmbpg_start_date').val(data.start_date || '');
        $('#mmbpg_end_date').val(data.end_date || '');
        $('input[name="post_category"]').val([data.post_category || 0]);
        $('#mmbpg_post_tags').val(data.post_tags || '');
        $('#mmbpg_seo_lb_phone').val(data.seo_lb_phone || '0822-3356-6320');

        $('.mmbpg-image-gallery').html(undoState.galleryHtml || '');

        checkRequiredFields();
    }

    function resetForm() {
        $('#mmbpg-form')[0].reset();
        if (typeof tinymce !== 'undefined' && tinymce.get('mmbpg_post_content')) {
            tinymce.get('mmbpg_post_content').setContent('');
        }
        $('.mmbpg-image-gallery').empty();
        $('#mmbpg_featured_images').val('').trigger('change');
        $('input[name="post_category"]').prop('checked', false);
        checkRequiredFields();
    }

    function checkRequiredFields() {
        let allFilled = true;
        $('.required-field').each(function () {
            if ($(this).val() === '' || $(this).val() === null) {
                allFilled = false;
            }
        });
        if ((typeof tinymce !== 'undefined' && tinymce.get('mmbpg_post_content') && tinymce.get('mmbpg_post_content').getContent({ format: 'text' }).trim() === '')) {
            allFilled = false;
        }
        if ($('.required-field-radio').length > 0 && $('input[name="post_category"]:checked').length === 0) {
            allFilled = false;
        }
        $('#mmbpg-start-button').prop('disabled', !allFilled);
    }

    $('.mmbpg-datepicker').datepicker({ dateFormat: 'yy-mm-dd', onSelect: checkRequiredFields });
    $('#mmbpg-form').on('keyup change', 'input, textarea, select', checkRequiredFields);
    if (typeof tinymce !== 'undefined') {
        tinymce.on('addeditor', function (e) {
            if (e.editor.id === 'mmbpg_post_content') { e.editor.on('keyup change', checkRequiredFields); }
        });
    }
    checkRequiredFields();

    $('#mmbpg-save-button').on('click', function () {
        const button = $(this);
        button.prop('disabled', true).text('Menyimpan...');

        $.post(mmbpg_ajax_obj.ajax_url, {
            action: 'mmbpg_save_settings',
            nonce: mmbpg_ajax_obj.nonce,
            settings: getFormData()
        }, function (response) {
            if (response.success) {
                showNotice(mmbpg_ajax_obj.i18n.settings_saved, 'success');
            } else {
                showNotice(response.data.message, 'error');
            }
        }).fail(function () {
            showNotice(mmbpg_ajax_obj.i18n.error_occurred, 'error');
        }).always(function () {
            button.prop('disabled', false).text('Simpan Template');
        });
    });

    $('#mmbpg-reset-button').on('click', function () {
        if (!confirm(mmbpg_ajax_obj.i18n.confirm_reset)) return;
        undoState = getFormData();
        undoState.galleryHtml = $('.mmbpg-image-gallery').html();
        resetForm();
        showNotice(mmbpg_ajax_obj.i18n.settings_reset, 'warning');
        $('#mmbpg-reset-button').hide();
        $('#mmbpg-undo-reset-button').show();
    });

    $('#mmbpg-undo-reset-button').on('click', function () {
        populateForm(undoState);
        undoState = {};
        $('#mmbpg-undo-reset-button').hide();
        $('#mmbpg-reset-button').show();
        showNotice('Reset dibatalkan.', 'info');
    });

    $('#mmbpg_erase_data_on_uninstall').on('change', function () {
        const isChecked = $(this).is(':checked') ? 'yes' : 'no';
        $.post(mmbpg_ajax_obj.ajax_url, {
            action: 'mmbpg_save_uninstall_setting',
            nonce: mmbpg_ajax_obj.nonce,
            value: isChecked
        });
    });

    let mediaUploader;
    $('#mmbpg-upload-image-button').on('click', function (e) {
        e.preventDefault();
        if (mediaUploader) { mediaUploader.open(); return; }
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Pilih Gambar', button: { text: 'Gunakan Gambar' }, multiple: true
        });
        mediaUploader.on('select', function () {
            const attachments = mediaUploader.state().get('selection').toJSON();
            let image_ids = $('#mmbpg_featured_images').val() ? $('#mmbpg_featured_images').val().split(',').filter(id => id) : [];

            attachments.forEach(function (attachment) {
                if (!image_ids.includes(attachment.id.toString())) { image_ids.push(attachment.id.toString()); }
            });

            $('#mmbpg_featured_images').val(image_ids.join(','));

            $('.mmbpg-image-gallery').empty();
            image_ids.forEach(function (id) {
                const attachment = attachments.find(att => att.id.toString() === id);
                if (attachment) {
                    $('.mmbpg-image-gallery').append(
                        `<div class="image-container" data-id="${id}"><img src="${attachment.sizes.thumbnail.url}" /><span class="remove-image">x</span></div>`
                    );
                }
            });

            checkRequiredFields();
        });
        mediaUploader.open();
    });

    $('.mmbpg-image-gallery').on('click', '.remove-image', function () {
        const container = $(this).closest('.image-container');
        const idToRemove = container.data('id').toString();
        let image_ids = $('#mmbpg_featured_images').val().split(',');
        image_ids = image_ids.filter(id => id !== idToRemove);
        $('#mmbpg_featured_images').val(image_ids.join(',')).trigger('change');
        container.remove();
    });


    $('#mmbpg-form').on('submit', function (e) {
        e.preventDefault();

        $.post(mmbpg_ajax_obj.ajax_url, {
            action: 'mmbpg_save_settings',
            nonce: mmbpg_ajax_obj.nonce,
            settings: getFormData()
        }).done(function (response) {
            if (response.success) {
                startGenerationProcess();
            } else {
                showNotice('Gagal menyimpan template sebelum memulai. Proses dibatalkan.', 'error');
            }
        }).fail(function () {
            showNotice('Error koneksi saat menyimpan template. Proses dibatalkan.', 'error');
        });
    });

    function startGenerationProcess() {
        const form = $('#mmbpg-form');
        const progressBarContainer = $('.mmbpg-progress-bar-container');
        const progressBar = $('.mmbpg-progress-bar');
        const progressStatus = $('.mmbpg-progress-status');

        form.addClass('processing');
        $('.mmbpg-actions button').prop('disabled', true);
        progressBarContainer.show();
        progressBar.css('width', '0%').text('0%');
        progressStatus.text(mmbpg_ajax_obj.i18n.starting_process);
        $('#mmbpg-notice').hide();

        const generationData = getFormData();
        const locations = generationData.local_business_target.split('\n').filter(line => line.trim() !== '');
        const total_posts = locations.length;
        let posts_processed = 0;

        function processPost(index) {
            if (index >= total_posts) {
                form.removeClass('processing');
                $('.mmbpg-actions button').prop('disabled', false);
                checkRequiredFields();
                progressStatus.text(mmbpg_ajax_obj.i18n.process_complete);
                showNotice(mmbpg_ajax_obj.i18n.process_complete, 'success');
                return;
            }

            const ajaxData = {
                action: 'mmbpg_start_generation', nonce: mmbpg_ajax_obj.nonce, index: index, ...generationData
            };

            $.post(mmbpg_ajax_obj.ajax_url, ajaxData, function (response) {
                if (response.success) {
                    posts_processed++;
                    const progress = (posts_processed / total_posts) * 100;
                    progressBar.css('width', progress + '%').text(Math.round(progress) + '%');
                    progressStatus.html(response.data.message);
                    processPost(index + 1);
                } else {
                    form.removeClass('processing');
                    $('.mmbpg-actions button').prop('disabled', false);
                    checkRequiredFields();
                    progressBarContainer.hide();
                    showNotice(mmbpg_ajax_obj.i18n.error_occurred + ': ' + (response.data.message || ''), 'error');
                }
            }).fail(function () {
                form.removeClass('processing');
                $('.mmbpg-actions button').prop('disabled', false);
                checkRequiredFields();
                progressBarContainer.hide();
                showNotice(mmbpg_ajax_obj.i18n.error_occurred, 'error');
            });
        }

        processPost(0);
    }
});