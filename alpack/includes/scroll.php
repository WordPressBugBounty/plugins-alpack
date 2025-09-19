<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Scroll functionality for PressLearn Plugin
 */

function presslearn_is_plugin_active_for_scroll() {
    $scroll_depth_enabled = get_option('presslearn_scroll_depth_enabled', 'no');
    return $scroll_depth_enabled === 'yes';
}

/**
 * Scroll admin styles
 */
function presslearn_scroll_admin_styles() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-scroll-depth') === false) {
        return;
    }
    
    wp_register_style(
        'presslearn-scroll-admin-css',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION
    );
    wp_enqueue_style('presslearn-scroll-admin-css');
    
    $scroll_css = "
    .popup-editor-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 99999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .popup-editor-content {
        background-color: #fff;
        width: 80%;
        max-width: 900px;
        border-radius: 5px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }
    
    .popup-editor-header {
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .popup-editor-header h2 {
        margin: 0;
        font-size: 18px;
    }
    
    .close-popup-editor {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: #666;
    }
    
    .popup-editor-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .wp-editor-container {
        min-height: 400px;
    }
    
    #wp-popup_content_editor-wrap {
        min-height: 400px;
    }
    
    .mce-container iframe {
        min-height: 350px !important;
    }
    
    .popup-editor-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        text-align: right;
    }
    
    .popup-content-preview {
        margin-top: 15px;
        padding: 10px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
        border-radius: 3px;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .popup-preview-content {
        margin-top: 5px;
        padding: 10px;
        background-color: #fff;
        border: 1px dashed #ccc;
    }
    
    .popup-preview-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7);
        z-index: 99999;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .popup-preview-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        height: 100%;
    }
    
    .popup-preview-window {
        background-color: #fff;
        width: 90%;
        max-width: 500px;
        border-radius: 8px;
        box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
        display: flex;
        flex-direction: column;
        max-height: 80vh;
        animation: popupFadeIn 0.3s ease-out;
    }
    
    @keyframes popupFadeIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes popupSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes popupZoomIn {
        from {
            opacity: 0;
            transform: scale(0.5);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    .popup-preview-header {
        padding: 15px 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f9f9f9;
        border-radius: 8px 8px 0 0;
    }
    
    .popup-preview-header h2 {
        margin: 0;
        font-size: 18px;
        color: #333;
    }
    
    .close-popup-preview {
        position: fixed;
        top: 20px;
        right: 20px;
        background: none;
        border: none;
        font-size: 40px;
        cursor: pointer;
        color: #fff;
        z-index: 100000;
        padding: 0;
        line-height: 1;
    }
    
    .close-popup-preview:hover {
        color: #ddd;
    }
    
    .popup-preview-body {
        padding: 30px;
        overflow-y: auto;
        flex: 1;
        line-height: 1.6;
    }
    
    .popup-preview-body img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
    
    .popup-preview-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        text-align: center;
        background-color: #f9f9f9;
        border-radius: 0 0 8px 8px;
    }
    
    @media screen and (max-width: 768px) {
        .popup-preview-window {
            width: 95%;
            max-width: 95%;
        }
        
        .popup-preview-body {
            padding: 20px;
        }
        
        .close-popup-preview {
            top: 10px;
            right: 10px;
            font-size: 32px;
        }
        
        .popup-editor-content {
            width: 95%;
            max-width: 95%;
        }
    }
    ";
    
    wp_add_inline_style('presslearn-scroll-admin-css', $scroll_css);
}

/**
 * Scroll admin scripts
 */
function presslearn_scroll_admin_scripts() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-scroll-depth') === false) {
        return;
    }
    
    wp_register_script(
        'presslearn-scroll-popup-editor-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-scroll-popup-editor-js');
    
    $popup_editor_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const openEditorBtn = document.getElementById('open-popup-editor');
        const closeEditorBtns = document.querySelectorAll('.close-popup-editor');
        const editorModal = document.getElementById('popup-editor-modal');
        const saveContentBtn = document.querySelector('.save-popup-content');
        
        if (openEditorBtn) {
            openEditorBtn.addEventListener('click', function(e) {
                e.preventDefault();
                editorModal.style.display = 'flex';
            });
        }
        
        closeEditorBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                editorModal.style.display = 'none';
            });
        });
        
        if (editorModal) {
            editorModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        }
        
        if (saveContentBtn) {
            saveContentBtn.addEventListener('click', function() {
                let content = '';
                if (typeof tinyMCE !== 'undefined' && tinyMCE.get('popup_content_editor') !== null) {
                    content = tinyMCE.get('popup_content_editor').getContent();
                } else {
                    const editor = document.getElementById('popup_content_editor');
                    if (editor) {
                        content = editor.value;
                    }
                }
                
                const hiddenInput = document.getElementById('popup_content_hidden');
                if (hiddenInput) {
                    hiddenInput.value = content;
                }
                
                let previewBtn = document.getElementById('preview-popup');
                if (content.trim() === '') {
                    if (previewBtn) {
                        previewBtn.remove();
                    }
                } else {
                    if (!previewBtn) {
                        previewBtn = document.createElement('button');
                        previewBtn.type = 'button';
                        previewBtn.id = 'preview-popup';
                        previewBtn.className = 'secondary-btn';
                        previewBtn.style.marginLeft = '5px';
                        previewBtn.textContent = '팝업 미리보기';
                        
                        if (openEditorBtn) {
                            openEditorBtn.parentNode.insertBefore(previewBtn, openEditorBtn.nextSibling);
                        }
                        
                        attachPreviewButtonEvent();
                    }
                }
                
                editorModal.style.display = 'none';
            });
        }
        
        function attachPreviewButtonEvent() {
            const previewBtn = document.getElementById('preview-popup');
            if (previewBtn) {
                previewBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const previewModal = document.getElementById('popup-preview-modal');
                    if (previewModal) {
                        previewModal.style.display = 'flex';
                        
                        const animationSelect = document.querySelector('select[name=\"popup_animation\"]');
                        const previewWindow = document.querySelector('.popup-preview-window');
                        
                        if (animationSelect && previewWindow) {
                            const animation = animationSelect.value;
                            previewWindow.style.animation = '';
                            
                            if (animation === 'fade') {
                                previewWindow.style.animation = 'popupFadeIn 0.3s ease-out';
                            } else if (animation === 'slide') {
                                previewWindow.style.animation = 'popupSlideIn 0.3s ease-out';
                            } else if (animation === 'zoom') {
                                previewWindow.style.animation = 'popupZoomIn 0.3s ease-out';
                            }
                        }
                    }
                });
            }
        }
        
        attachPreviewButtonEvent();
    });
    ";
    
    wp_add_inline_script('presslearn-scroll-popup-editor-js', $popup_editor_js);
    
    wp_register_script(
        'presslearn-scroll-popup-preview-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-scroll-popup-preview-js');
    
    $popup_preview_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const closePreviewBtns = document.querySelectorAll('.close-popup-preview');
        const previewModal = document.getElementById('popup-preview-modal');
        
        closePreviewBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                if (previewModal) {
                    previewModal.style.display = 'none';
                }
            });
        });
        
        if (previewModal) {
            previewModal.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('popup-preview-container')) {
                    this.style.display = 'none';
                }
            });
        }
    });
    ";
    
    wp_add_inline_script('presslearn-scroll-popup-preview-js', $popup_preview_js);
}

add_action('admin_enqueue_scripts', 'presslearn_scroll_admin_styles');
add_action('admin_enqueue_scripts', 'presslearn_scroll_admin_scripts');

