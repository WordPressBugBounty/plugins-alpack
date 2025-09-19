<?php
if (!defined('ABSPATH')) {
    exit;
}

function presslearn_is_plugin_active_for_dynamic() {
    $dynamic_banner_enabled = get_option('presslearn_dynamic_banner_enabled', 'no');
    return $dynamic_banner_enabled === 'yes';
}

function presslearn_dynamic_banner_admin_styles() {
    if (!is_admin()) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-dynamic-banner') === false) {
        return;
    }

    wp_register_style(
        'presslearn-dynamic-banner-admin-css',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION
    );
    wp_enqueue_style('presslearn-dynamic-banner-admin-css');

    $dynamic_banner_css = "
    .presslearn-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .regular-input-form {
        width: 100% !important;
        box-sizing: border-box !important;
        border: 1px solid #eee !important;
        border-radius: 10px !important;
        padding: 10px !important;
        color: #000 !important;
    }

    .regular-input-form::placeholder {
        color: #8b95a1 !important;
    }

    .regular-input-form:focus {
        border-color: transparent !important;
        outline: 2px solid #000 !important;
    }

    input[readonly].regular-input-form {
        background-color: #f8f8f8;
        cursor: not-allowed;
    }

    .presslearn-modal-content {
        background-color: #fff;
        width: 90%;
        max-width: 800px;
        border-radius: 6px;
        box-shadow: 0 3px 20px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
        min-height: 700px;
    }

    .presslearn-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .presslearn-modal-header h3 {
        margin: 0;
        font-size: 20px;
    }

    .presslearn-modal-close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #666;
    }

    .presslearn-modal-close:hover {
        color: #000;
    }

    .presslearn-modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .presslearn-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        text-align: right;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    
    .campaign-tabs {
        margin-top: 10px;
        margin-bottom: 20px;
    }
    
    .campaign-tab {
        display: inline-block;
        padding: 10px 20px;
        margin: 0;
        margin-right: 5px;
        font-weight: bold;
        text-decoration: none;
        background-color: #f8f9fa;
        border-radius: 100px;
        color: #000;
        border: 0;
        cursor: pointer;
    }
    
    .campaign-tab.active {
        background: #000;
        color: #fff;
    }
    
    .campaign-tab-content {
        display: none;
    }
    
    .campaign-tab-content.active {
        display: block;
    }
    
    .campaign-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .campaign-table th,
    .campaign-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .campaign-table th {
        background-color: #f8f8f8;
        font-weight: 600;
    }
    
    .campaign-table .no-campaigns {
        text-align: center;
        padding: 30px;
        color: #888;
    }
    
    .settings-form-row {
        margin-bottom: 20px;
    }
    
    .settings-form-row label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .banner-upload-container {
        display: flex;
        gap: 10px;
    }
    
    .banner-upload-container input {
        flex: 1;
    }
    
    .banner-preview {
        margin-top: 10px;
        max-width: 300px;
        border: 1px solid #ddd;
        padding: 5px;
        border-radius: 4px;
    }
    
    .banner-preview img {
        max-width: 100%;
        height: auto;
    }
    
    .campaign-actions {
        display: flex;
        gap: 5px;
    }
    
    .campaign-actions button {
        padding: 2px 8px;
        background: none;
        border: 1px solid #ddd;
        border-radius: 3px;
        cursor: pointer;
    }
    
    .campaign-actions button.edit-campaign {
        color: #2196F3;
    }
    
    .campaign-actions button.delete-campaign {
        color: #f44336;
    }
    
    .form-actions {
        margin-top: 30px;
    }
    
    .campaign-shortcode {
        padding: 5px 8px;
        background-color: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-family: monospace;
        display: inline-block;
    }
    
    .campaign-status {
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .campaign-status.active {
        background-color: #e8f5e9;
        color: #2e7d32;
    }
    
    .campaign-status.inactive {
        background-color: #ffebee;
        color: #c62828;
    }
    
    .shortcode-container {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .copy-shortcode {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        border-radius: 3px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        cursor: pointer;
        color: #555;
        transition: all 0.2s ease;
    }
    
    .copy-shortcode:hover {
        background-color: #e0e0e0;
        color: #333;
    }
    
    .copy-success {
        background-color: #e8f5e9;
        border: 1px solid #a5d6a7;
        color: #2e7d32;
    }
    
    .banner-type-selector {
        display: flex;
        gap: 10px;
        margin-top: 5px;
        justify-content: space-between;
        align-items: center;
    }
    
    .banner-type-option {
        width: 100%;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .banner-type-option input[type=\"radio\"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .banner-type-btn {
        flex: 1;
        display: block;
        padding: 12px 25px;
        background-color: #f5f5f5;
        border: 0;
        border-radius: 10px;
        font-weight: 500;
        font-size: 14px;
        text-align: center;
        color: #555;
        transition: all 0.3s ease;
        min-width: 120px;
    }
    
    .banner-type-option:hover .banner-type-btn {
        background-color: #e9e9e9;
        border-color: #ccc;
    }
    
    .banner-type-option input[type=\"radio\"]:checked + .banner-type-btn {
        background-color: #2196F3;
        border-color: #2196F3;
        color: white;
        font-weight: 600;
    }
    
    .banner-dimensions {
        display: flex;
        gap: 20px;
    }
    
    .dimension-field {
        flex: 1;
    }
    
    .required {
        color: #f44336;
        margin-left: 3px;
    }
    
    .code-editor-container {
        margin-bottom: 10px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #ddd;
        line-height: 1;
    }
    
    .code-editor-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f3f3f3;
        padding: 8px 12px;
        border-bottom: 1px solid #ddd;
        font-family: monospace;
        color: #555;
        font-size: 12px;
    }
    
    .code-copy-btn {
        display: flex;
        align-items: center;
        gap: 5px;
        background: none;
        border: none;
        font-size: 12px;
        color: #666;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
    }
    
    .code-copy-btn:hover {
        background-color: #e0e0e0;
    }
    
    .code-editor {
        width: 100%;
        border: none;
        background-color: #fafafa;
        font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
        font-size: 13px;
        color: #333;
        padding: 15px;
        resize: vertical;
        box-sizing: border-box;
        line-height: 1.5;
        tab-size: 4;
    }
    
    .code-editor:focus {
        outline: none;
        background-color: #fff;
    }
    
    .form-hint {
        margin-top: 5px;
        font-size: 12px;
        color: #666;
    }
    
    .file-upload-progress {
        margin-top: 8px;
        height: 4px;
        background-color: #f1f1f1;
        border-radius: 2px;
        overflow: hidden;
        width: 100%;
    }
    
    .progress-bar {
        height: 100%;
        width: 0;
        background-color: #2196F3;
        transition: width 0.3s ease;
    }
    
    .file-upload-info {
        margin-top: 8px;
        padding: 8px 12px;
        background-color: #f8f8f8;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        font-size: 13px;
    }
    
    .file-name {
        font-weight: 500;
        word-break: break-all;
    }

    .edit-modal-overlay {
        overflow-y: auto;
    }

    .edit-modal-overlay .presslearn-modal-content {
        max-width: 700px;
        margin: 30px auto;
    }

    #edit_banner_preview {
        margin-top: 10px;
        max-width: 100%;
        text-align: center;
    }

    #edit_banner_preview img {
        max-width: 100%;
        max-height: 300px;
        border: 1px dashed #ccc;
    }
    ";

    wp_add_inline_style('presslearn-dynamic-banner-admin-css', $dynamic_banner_css);
}

function presslearn_dynamic_banner_admin_scripts() {
    if (!is_admin()) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-dynamic-banner') === false) {
        return;
    }

    wp_register_script(
        'presslearn-dynamic-banner-modal-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-dynamic-banner-modal-js');

    $modal_js = "
    jQuery(document).ready(function($) {
        $('#open-shortcode-modal').on('click', function() {
            $('#shortcode-modal').show();
            loadCampaigns();
            resetCampaignForm();
        });
        
        $('.presslearn-modal-close, #close-modal-button').on('click', function() {
            $('#shortcode-modal').hide();
        });
        
        $(window).on('click', function(event) {
            if ($(event.target).is('.presslearn-modal')) {
                $('.presslearn-modal').hide();
            }
        });
        
        $('.campaign-tab').on('click', function() {
            const tab = $(this).data('tab');
            
            $('.campaign-tab').removeClass('active');
            $(this).addClass('active');
            
            $('.campaign-tab-content').removeClass('active');
            $('#' + tab + '-tab').addClass('active');
            
            if (tab === 'add-campaign') {
                resetCampaignForm();
            }
        });
        
        function resetCampaignForm() {
            $('#add-campaign-form')[0].reset();
            
            $('input[name=\"banner_type\"][value=\"custom\"]').prop('checked', true).trigger('change');
            
            $('#banner-preview, #cover-banner-preview, #iframe-cover-banner-preview').hide();
            $('#banner-preview img, #cover-banner-preview img, #iframe-cover-banner-preview img').attr('src', '');
            
            $('#campaign_banner_url, #campaign_cover_banner_url, #iframe_campaign_cover_banner_url').val('');
            $('#campaign_name, #campaign_link, #banner_width, #banner_height').val('');
            $('#iframe_code, #iframe_link_url, #iframe_width, #iframe_height').val('');
            
            $('.file-upload-info').hide();
            
            userModifiedWidth = false;
            userModifiedHeight = false;
            
            $('#campaign_id').remove();
            
            $('#add-campaign-form button[type=\"submit\"]').text('캠페인 추가');
        }
        
        window.resetCampaignForm = resetCampaignForm;
        window.loadCampaigns = loadCampaigns;
    });
    ";

    wp_add_inline_script('presslearn-dynamic-banner-modal-js', $modal_js);

    wp_register_script(
        'presslearn-dynamic-banner-upload-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-dynamic-banner-upload-js');

    $upload_js = "
    jQuery(document).ready(function($) {
        $('#upload-banner').on('click', function(e) {
            e.preventDefault();
            $('#banner_image_file').click();
        });
        
        $('#upload-cover-banner').on('click', function(e) {
            e.preventDefault();
            $('#cover_banner_image_file').click();
        });
        
        $('#iframe-upload-cover-banner').on('click', function(e) {
            e.preventDefault();
            $('#iframe_cover_banner_image_file').click();
        });
        
        $('#banner_image_file').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (!file.type.match('image.*')) {
                alert('이미지 파일만 업로드 가능합니다.');
                return;
            }
            
            $(this).siblings('.file-upload-info').find('.file-name').text(file.name);
            $(this).siblings('.file-upload-info').show();
            $(this).siblings('.file-upload-info').find('.progress-bar').css('width', '0%');
            
            const formData = new FormData();
            formData.append('action', 'presslearn_upload_banner');
            formData.append('banner_image', file);
            formData.append('nonce', '" . wp_create_nonce("presslearn_upload_banner_nonce") . "');
            
            const currentUploadContainer = $(this).closest('.settings-form-row');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            currentUploadContainer.find('.progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $('#campaign_banner_url').val(response.data.url);
                        $('#banner-preview').show().find('img').attr('src', response.data.url);
                        currentUploadContainer.find('.file-upload-info').hide();
                    } else {
                        alert(response.data.message || '이미지 업로드에 실패했습니다.');
                        currentUploadContainer.find('.file-upload-info').hide();
                    }
                },
                error: function() {
                    alert('서버 통신 중 오류가 발생했습니다. 나중에 다시 시도하세요.');
                    currentUploadContainer.find('.file-upload-info').hide();
                }
            });
        });
        
        $('#cover_banner_image_file').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (!file.type.match('image.*')) {
                alert('이미지 파일만 업로드 가능합니다.');
                return;
            }
            
            $(this).siblings('.file-upload-info').find('.file-name').text(file.name);
            $(this).siblings('.file-upload-info').show();
            $(this).siblings('.file-upload-info').find('.progress-bar').css('width', '0%');
            
            const formData = new FormData();
            formData.append('action', 'presslearn_upload_banner');
            formData.append('banner_image', file);
            formData.append('banner_type', 'cover');
            formData.append('nonce', '" . wp_create_nonce("presslearn_upload_banner_nonce") . "');
            
            const currentUploadContainer = $(this).closest('.settings-form-row');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            currentUploadContainer.find('.progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $('#campaign_cover_banner_url').val(response.data.url);
                        $('#cover-banner-preview').show().find('img').attr('src', response.data.url);
                        currentUploadContainer.find('.file-upload-info').hide();
                    } else {
                        alert(response.data.message || '커버 배너 이미지 업로드에 실패했습니다.');
                        currentUploadContainer.find('.file-upload-info').hide();
                    }
                },
                error: function() {
                    alert('서버 통신 중 오류가 발생했습니다. 나중에 다시 시도하세요.');
                    currentUploadContainer.find('.file-upload-info').hide();
                }
            });
        });
        
        $('#iframe_cover_banner_image_file').on('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            if (!file.type.match('image.*')) {
                alert('이미지 파일만 업로드 가능합니다.');
                return;
            }
            
            $(this).siblings('.file-upload-info').find('.file-name').text(file.name);
            $(this).siblings('.file-upload-info').show();
            $(this).siblings('.file-upload-info').find('.progress-bar').css('width', '0%');
            
            const formData = new FormData();
            formData.append('action', 'presslearn_upload_banner');
            formData.append('banner_image', file);
            formData.append('banner_type', 'cover');
            formData.append('nonce', '" . wp_create_nonce("presslearn_upload_banner_nonce") . "');
            
            const currentUploadContainer = $(this).closest('.settings-form-row');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = (evt.loaded / evt.total) * 100;
                            currentUploadContainer.find('.progress-bar').css('width', percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if (response.success && response.data.url) {
                        $('#iframe_campaign_cover_banner_url').val(response.data.url);
                        $('#iframe-cover-banner-preview').show().find('img').attr('src', response.data.url);
                        currentUploadContainer.find('.file-upload-info').hide();
                    } else {
                        alert(response.data.message || '커버 배너 이미지 업로드에 실패했습니다.');
                        currentUploadContainer.find('.file-upload-info').hide();
                    }
                },
                error: function() {
                    alert('서버 통신 중 오류가 발생했습니다. 나중에 다시 시도하세요.');
                    currentUploadContainer.find('.file-upload-info').hide();
                }
            });
        });
    });
    ";

    wp_add_inline_script('presslearn-dynamic-banner-upload-js', $upload_js);

    wp_register_script(
        'presslearn-dynamic-banner-type-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-dynamic-banner-type-js');

    $type_js = "
    jQuery(document).ready(function($) {
        $('input[name=\"banner_type\"]').on('change', function() {
            const bannerType = $('input[name=\"banner_type\"]:checked').val();
            
            if (bannerType === 'custom') {
                $('#custom-banner-fields').show();
                $('#iframe-banner-fields').hide();
                
                $('#banner_width, #banner_height, #campaign_banner_url, #campaign_link').prop('required', true);
                $('#iframe_width, #iframe_height, #iframe_code').prop('required', false);
            } else if (bannerType === 'iframe') {
                $('#custom-banner-fields').hide();
                $('#iframe-banner-fields').show();
                
                $('#iframe_width, #iframe_height, #iframe_code').prop('required', true);
                $('#banner_width, #banner_height, #campaign_banner_url, #campaign_link').prop('required', false);
            }
        });
        
        function extractDimensionsFromIframe(iframeCode) {
            let width = null;
            let height = null;
            
            const widthMatch = iframeCode.match(/width=[\"']?(\\d+)[\"']?/i);
            if (widthMatch && widthMatch[1]) {
                width = parseInt(widthMatch[1], 10);
            }
            
            const heightMatch = iframeCode.match(/height=[\"']?(\\d+)[\"']?/i);
            if (heightMatch && heightMatch[1]) {
                height = parseInt(heightMatch[1], 10);
            }
            
            return { width, height };
        }
        
        let userModifiedWidth = false;
        let userModifiedHeight = false;
        
        $('#iframe_code').on('input', function() {
            const iframeCode = $(this).val();
            if (!iframeCode) return;
            
            const { width, height } = extractDimensionsFromIframe(iframeCode);
            
            if (!userModifiedWidth && width) {
                $('#iframe_width').val(width);
            }
            
            if (!userModifiedHeight && height) {
                $('#iframe_height').val(height);
            }
        });
        
        $('#iframe_width').on('input', function() {
            userModifiedWidth = true;
        });
        
        $('#iframe_height').on('input', function() {
            userModifiedHeight = true;
        });
        
        $('#add-campaign-form').on('reset', function() {
            userModifiedWidth = false;
            userModifiedHeight = false;
        });
        
        window.userModifiedWidth = userModifiedWidth;
        window.userModifiedHeight = userModifiedHeight;
    });
    ";

    wp_add_inline_script('presslearn-dynamic-banner-type-js', $type_js);

    wp_register_script(
        'presslearn-dynamic-banner-campaign-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-dynamic-banner-campaign-js');

    wp_localize_script('presslearn-dynamic-banner-campaign-js', 'pressleanDynamicBanner', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'campaign_nonce' => wp_create_nonce('presslearn_campaign_nonce')
    ));

    $campaign_js = "
    jQuery(document).ready(function($) {
        $(document).on('click', '.edit-campaign', function() {
            const campaignId = $(this).data('id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'presslearn_get_campaign',
                    nonce: pressleanDynamicBanner.campaign_nonce,
                    id: campaignId
                },
                success: function(response) {
                    if (response.success) {
                        const campaign = response.data.campaign;
                        
                        $('.campaign-tab[data-tab=\"add-campaign\"]').click();
                        
                        $('#campaign_name').val(campaign.name);
                        
                        if ($('#campaign_id').length === 0) {
                            $('#add-campaign-form').prepend('<input type=\"hidden\" id=\"campaign_id\" name=\"campaign_id\" value=\"' + campaign.id + '\">');
                        } else {
                            $('#campaign_id').val(campaign.id);
                        }
                        
                        $('#add-campaign-form button[type=\"submit\"]').text('캠페인 수정');
                        
                        $('input[name=\"banner_type\"][value=\"' + campaign.type + '\"]').prop('checked', true).trigger('change');
                        
                        if (campaign.type === 'custom') {
                            $('#campaign_banner_url').val(campaign.banner_url);
                            $('#campaign_link').val(campaign.link);
                            $('#banner_width').val(campaign.width);
                            $('#banner_height').val(campaign.height);
                            
                            if (campaign.banner_url) {
                                $('#banner-preview').show().find('img').attr('src', campaign.banner_url);
                            }
                            
                            if (campaign.cover_banner_url) {
                                $('#campaign_cover_banner_url').val(campaign.cover_banner_url);
                                $('#cover-banner-preview').show().find('img').attr('src', campaign.cover_banner_url);
                            }
                        } else if (campaign.type === 'iframe') {
                            setTimeout(function() {
                                $('#iframe_code').val(campaign.iframe_code);
                                $('#iframe_link_url').val(campaign.link);
                                $('#iframe_width').val(campaign.width);
                                $('#iframe_height').val(campaign.height);
                                
                                if (campaign.cover_banner_url) {
                                    $('#iframe_campaign_cover_banner_url').val(campaign.cover_banner_url);
                                    $('#iframe-cover-banner-preview').show().find('img').attr('src', campaign.cover_banner_url);
                                }
                            }, 100);
                        }
                    } else {
                        alert(response.data.message || '캠페인 정보를 가져오는 중 오류가 발생했습니다.');
                    }
                },
                error: function() {
                    alert('서버 통신 오류가 발생했습니다.');
                }
            });
        });
        
        $('#add-campaign-form').on('submit', function(e) {
            e.preventDefault();
            
            const campaignName = $('#campaign_name').val();
            const bannerType = $('input[name=\"banner_type\"]:checked').val();
            const campaignId = $('#campaign_id').val();
            
            let isValid = true;
            let formData = {
                name: campaignName,
                type: bannerType
            };
            
            if (campaignId) {
                formData.id = campaignId;
            }
            
            if (!campaignName) {
                alert('캠페인 이름을 입력해주세요.');
                isValid = false;
                return;
            }
            
            if (bannerType === 'custom') {
                const bannerUrl = $('#campaign_banner_url').val();
                const campaignLink = $('#campaign_link').val();
                const bannerWidth = $('#banner_width').val();
                const bannerHeight = $('#banner_height').val();
                
                if (!bannerUrl) {
                    alert('배너 이미지 URL을 입력해주세요.');
                    isValid = false;
                    return;
                }
                
                if (!campaignLink) {
                    alert('링크 URL을 입력해주세요.');
                    isValid = false;
                    return;
                }
                
                if (!bannerWidth || !bannerHeight) {
                    alert('배너 가로/세로 크기를 입력해주세요.');
                    isValid = false;
                    return;
                }
                
                formData.banner_url = bannerUrl;
                formData.link = campaignLink;
                formData.width = bannerWidth;
                formData.height = bannerHeight;
                
                const coverBannerUrl = $('#campaign_cover_banner_url').val();
                if (coverBannerUrl) {
                    formData.cover_banner_url = coverBannerUrl;
                }
            } else if (bannerType === 'iframe') {
                const iframeCode = $('#iframe_code').val();
                const iframeLinkUrl = $('#iframe_link_url').val();
                const iframeWidth = $('#iframe_width').val();
                const iframeHeight = $('#iframe_height').val();
                
                if (!iframeCode) {
                    alert('iframe 코드를 입력해주세요.');
                    isValid = false;
                    return;
                }
                
                if (!iframeWidth || !iframeHeight) {
                    alert('iframe 가로/세로 크기를 입력해주세요.');
                    isValid = false;
                    return;
                }
                
                formData.iframe_code = iframeCode;
                formData.width = iframeWidth;
                formData.height = iframeHeight;
                
                if (iframeLinkUrl) {
                    formData.link = iframeLinkUrl;
                }
                
                const coverBannerUrl = $('#iframe_campaign_cover_banner_url').val();
                if (coverBannerUrl) {
                    formData.cover_banner_url = coverBannerUrl;
                }
            }
            
            if (!isValid) return;
            
            const action = campaignId ? 'presslearn_update_campaign' : 'presslearn_add_campaign';
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: pressleanDynamicBanner.campaign_nonce,
                    campaign_data: formData
                },
                beforeSend: function() {
                    $('#add-campaign-form button[type=\"submit\"]').prop('disabled', true).text('처리 중...');
                },
                success: function(response) {
                    if (response.success) {
                        let message = campaignId ? '캠페인이 수정되었습니다.' : '캠페인이 추가되었습니다.';
                        alert(message);
                        
                        loadCampaigns();
                        
                        resetCampaignForm();
                        
                        $('.campaign-tab[data-tab=\"campaigns\"]').click();
                    } else {
                        alert(response.data.message || '캠페인 저장 중 오류가 발생했습니다.');
                    }
                },
                error: function() {
                    alert('서버 통신 오류가 발생했습니다.');
                },
                complete: function() {
                    let buttonText = campaignId ? '캠페인 수정' : '캠페인 추가';
                    $('#add-campaign-form button[type=\"submit\"]').prop('disabled', false).text(buttonText);
                }
            });
        });
        
        function loadCampaigns() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'presslearn_get_campaigns',
                    nonce: pressleanDynamicBanner.campaign_nonce
                },
                beforeSend: function() {
                    $('#campaign-list').html('<tr><td colspan=\"4\" style=\"text-align:center;padding:20px;\">데이터를 불러오는 중입니다...</td></tr>');
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.campaigns && response.data.campaigns.length > 0) {
                            let campaignsHtml = '';
                            
                            $.each(response.data.campaigns, function(index, campaign) {
                                campaignsHtml += `
                                    <tr data-id=\"\${campaign.id}\">
                                        <td>\${campaign.name}</td>
                                        <td>
                                            <div class=\"shortcode-container\">
                                                <span class=\"campaign-shortcode\">\${campaign.shortcode}</span>
                                                <button type=\"button\" class=\"copy-shortcode\" title=\"숏코드 복사\">
                                                    <svg width=\"14\" height=\"14\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">
                                                        <path d=\"M16 1H4C2.9 1 2 1.9 2 3V17H4V3H16V1ZM19 5H8C6.9 5 6 5.9 6 7V21C6 22.1 6.9 23 8 23H19C20.1 23 21 22.1 21 21V7C21 5.9 20.1 5 19 5ZM19 21H8V7H19V21Z\" fill=\"currentColor\"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class=\"campaign-actions\">
                                                <button type=\"button\" class=\"edit-campaign\" data-id=\"\${campaign.id}\">수정</button>
                                                <button type=\"button\" class=\"delete-campaign\" data-id=\"\${campaign.id}\">삭제</button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            
                            $('#campaign-list').html(campaignsHtml);
                        } else {
                            $('#campaign-list').html('<tr><td colspan=\"4\" class=\"no-campaigns\">등록된 캠페인이 없습니다. 새 캠페인을 추가해주세요.</td></tr>');
                        }
                    } else {
                        alert(response.data.message || '캠페인 목록을 불러오는 중 오류가 발생했습니다.');
                        $('#campaign-list').html('<tr><td colspan=\"4\" class=\"no-campaigns\">캠페인 목록을 불러올 수 없습니다.</td></tr>');
                    }
                },
                error: function() {
                    alert('서버 통신 오류가 발생했습니다.');
                    $('#campaign-list').html('<tr><td colspan=\"4\" class=\"no-campaigns\">서버 통신 오류가 발생했습니다.</td></tr>');
                }
            });
        }
        
        $(document).on('click', '.copy-shortcode', function() {
            const shortcode = $(this).prev('.campaign-shortcode').text();
            const copyButton = $(this);
            
            navigator.clipboard.writeText(shortcode).then(function() {
                copyButton.addClass('copy-success');
                
                setTimeout(function() {
                    copyButton.removeClass('copy-success');
                }, 1500);
            });
        });
        
        $(document).on('click', '.delete-campaign', function() {
            const row = $(this).closest('tr');
            const name = row.find('td:first').text();
            const campaignId = $(this).data('id');
            
            if (confirm('정말로 \"' + name + '\" 캠페인을 삭제하시겠습니까?')) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'presslearn_delete_campaign',
                        nonce: pressleanDynamicBanner.campaign_nonce,
                        id: campaignId
                    },
                    success: function(response) {
                        if (response.success) {
                            row.remove();
                            
                            if ($('#campaign-list tr').length === 0) {
                                $('#campaign-list').html('<tr><td colspan=\"5\" class=\"no-campaigns\">등록된 캠페인이 없습니다. 새 캠페인을 추가해주세요.</td></tr>');
                            }
                            
                            alert('캠페인이 삭제되었습니다.');
                        } else {
                            alert(response.data.message || '캠페인 삭제 중 오류가 발생했습니다.');
                        }
                    },
                    error: function() {
                        alert('서버 통신 오류가 발생했습니다.');
                    }
                });
            }
        });
        
        window.loadCampaigns = loadCampaigns;
    });
    ";

    wp_add_inline_script('presslearn-dynamic-banner-campaign-js', $campaign_js);
}

add_action('admin_enqueue_scripts', 'presslearn_dynamic_banner_admin_styles');
add_action('admin_enqueue_scripts', 'presslearn_dynamic_banner_admin_scripts');

