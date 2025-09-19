<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_scroll_depth']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_scroll_depth_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_scroll_depth_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_scroll_depth_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'scroll_depth' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_scroll_depth_enabled', $enable);
        $updated = true;
    }
}

if (isset($_POST['save_scroll_settings'])) {
    check_admin_referer('presslearn_save_scroll_settings_nonce');
    
    if (current_user_can('manage_options')) {
        $scroll_percentage = isset($_POST['scroll_percentage']) ? intval(wp_unslash($_POST['scroll_percentage'])) : 50;
        if ($scroll_percentage < 1) $scroll_percentage = 1;
        if ($scroll_percentage > 100) $scroll_percentage = 100;
        
        $popup_animation = isset($_POST['popup_animation']) ? sanitize_text_field(wp_unslash($_POST['popup_animation'])) : 'fade';
        $repeat_setting = isset($_POST['repeat_setting']) ? sanitize_text_field(wp_unslash($_POST['repeat_setting'])) : 'once';
        
        update_option('presslearn_scroll_percentage', $scroll_percentage);
        update_option('presslearn_popup_animation', $popup_animation);
        update_option('presslearn_repeat_setting', $repeat_setting);
        
        if (isset($_POST['popup_content'])) {
            $popup_content = wp_kses_post(wp_unslash($_POST['popup_content']));
            update_option('presslearn_popup_content', $popup_content);
        }
        
        $settings_updated = true;
    }
}

$scroll_depth_enabled = get_option('presslearn_scroll_depth_enabled', 'no');
$scroll_percentage = get_option('presslearn_scroll_percentage', 50);
$popup_animation = get_option('presslearn_popup_animation', 'fade');
$repeat_setting = get_option('presslearn_repeat_setting', 'once');
$popup_content = get_option('presslearn_popup_content', '');

wp_enqueue_editor();
wp_enqueue_media();

?>
<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>스마트 스크롤</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($scroll_depth_enabled === 'yes'): ?>
        <div class="presslearn-header-status-item status-activate">
            <p>기능이 활성화되었습니다.</p>
        </div>
        <?php else: ?>
        <div class="presslearn-header-status-item status-deactivate">
            <p>기능이 비활성화 상태입니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="wrap">
    
    <div class="presslearn-breadcrumbs-wrap">
        <div class="presslearn-breadcrumbs">
            <span>대시보드</span>
            <span class="divider">/</span>
            <span class="active">스마트 스크롤</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>스마트 스크롤</h2>
            <p>스크롤에 따라서 자동으로 팝업을 띄워줄 수 있는 기능입니다. 단일 캠페인만 운영 가능합니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="scroll-depth-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_scroll_depth_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_scroll_depth" value="1">
                        <input type="hidden" name="enable" id="scroll-depth-enable-value" value="<?php echo esc_attr($scroll_depth_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($scroll_depth_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('scroll-depth-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('scroll-depth-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <form method="post" action="" id="scroll-settings-form">
                <?php wp_nonce_field('presslearn_save_scroll_settings_nonce'); ?>
                <input type="hidden" name="save_scroll_settings" value="1">
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>스크롤 퍼센트</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="number" class="regular-text" name="scroll_percentage" value="<?php echo esc_attr($scroll_percentage); ?>" min="1" max="100">
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>팝업 애니메이션</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="popup_animation" class="regular-text">
                            <option value="fade" <?php selected($popup_animation, 'fade'); ?>>Fade</option>
                            <option value="slide" <?php selected($popup_animation, 'slide'); ?>>Slide</option>
                            <option value="zoom" <?php selected($popup_animation, 'zoom'); ?>>Zoom</option>
                        </select>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>반복 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="repeat_setting" class="regular-text">
                            <option value="once" <?php selected($repeat_setting, 'once'); ?>>브라우저 당 한 번만 표시</option>
                            <option value="loop" <?php selected($repeat_setting, 'loop'); ?>>반복 표시</option>
                        </select>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>팝업 표시 내용</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="hidden" id="popup_content_hidden" name="popup_content" value="<?php echo esc_attr($popup_content); ?>">
                        <button type="button" id="open-popup-editor" class="point-btn">내용 설정하기</button>
                        <?php if (!empty($popup_content)): ?>
                        <button type="button" id="preview-popup" class="secondary-btn" style="margin-left: 5px;">팝업 미리보기</button>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">팝업 내용을 저장하고 미리보기를 클릭하여 확인 가능합니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="scroll-settings-form">저장하기</button>
            </div>
        </div>
    </div>
    
    <div id="popup-editor-modal" class="popup-editor-overlay" style="display:none;">
        <div class="popup-editor-content">
            <div class="popup-editor-header">
                <h2>팝업 내용 설정</h2>
                <button type="button" class="close-popup-editor">&times;</button>
            </div>
            <div class="popup-editor-body">
                <?php
                $editor_settings = array(
                    'textarea_name' => 'popup_content_editor',
                    'textarea_rows' => 20,
                    'editor_height' => 400,
                    'media_buttons' => true,
                    'tinymce'       => array(
                        'plugins'                       => 'lists,paste,tabfocus,wplink,wordpress',
                        'paste_as_text'                 => true,
                        'paste_auto_cleanup_on_paste'   => true,
                        'paste_remove_spans'            => true,
                        'paste_remove_styles'           => true,
                        'paste_remove_styles_if_webkit' => true,
                        'paste_strip_class_attributes'  => true,
                    ),
                );
                wp_editor($popup_content, 'popup_content_editor', $editor_settings);
                ?>
            </div>
            <div class="popup-editor-footer">
                <button type="button" class="button button-secondary close-popup-editor">취소</button>
                <button type="button" class="button button-primary save-popup-content">적용하기</button>
            </div>
        </div>
    </div>
    
    <div id="popup-preview-modal" class="popup-preview-overlay" style="display:none;">
        <button type="button" class="close-popup-preview">&times;</button>
        <div class="popup-preview-container">
            <div class="popup-preview-window">
                <div class="popup-preview-body">
                    <?php echo wp_kses_post(wpautop($popup_content)); ?>
                </div>
            </div>
        </div>
    </div>
</div> 
