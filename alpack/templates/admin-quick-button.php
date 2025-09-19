<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_quick_button']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_quick_button_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_quick_button_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_quick_button_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'quick_button' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_quick_button_enabled', $enable);
        $updated = true;
    }
}

if (isset($_POST['save_quick_button_settings'])) {
    check_admin_referer('presslearn_save_quick_button_settings_nonce');
    
    if (current_user_can('manage_options')) {
        $button_transition = isset($_POST['button_transition']) ? sanitize_text_field(wp_unslash($_POST['button_transition'])) : 'no';
        if ($button_transition === 'yes' || $button_transition === 'no') {
            update_option('presslearn_button_transition_enabled', $button_transition);
        }
        
        $settings_updated = true;
    }
}

$quick_button_enabled = get_option('presslearn_quick_button_enabled', 'no');
$button_transition_enabled = get_option('presslearn_button_transition_enabled', 'no');

?>
<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>빠른 버튼 생성</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($quick_button_enabled === 'yes'): ?>
        <div class="presslearn-header-status-item status-activate">
            <p>기능이 활성화 되었습니다.</p>
        </div>
        <?php else: ?>
        <div class="presslearn-header-status-item status-deactivate">
            <p>기능이 비활성화 되었습니다.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="wrap">
    <div class="presslearn-breadcrumbs-wrap">
        <div class="presslearn-breadcrumbs">
            <span>대시보드</span>
            <span class="divider">/</span>
            <span class="active">빠른 버튼 생성</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>빠른 버튼 생성</h2>
            <p>글 쓰기 화면에서 빠르게 버튼 생성 기능을 설정할 수 있습니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="quick-button-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_quick_button_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_quick_button" value="1">
                        <input type="hidden" name="enable" id="quick-button-enable-value" value="<?php echo esc_attr($quick_button_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($quick_button_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('quick-button-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('quick-button-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <form method="post" action="" id="quick-button-settings-form">
                <?php wp_nonce_field('presslearn_save_quick_button_settings_nonce'); ?>
                <input type="hidden" name="save_quick_button_settings" value="1">
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>버튼 애니메이션 무한 반복</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="button_transition">
                            <option value="yes" <?php selected($button_transition_enabled, 'yes'); ?>>활성화</option>
                            <option value="no" <?php selected($button_transition_enabled, 'no'); ?>>비활성화</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">글 작성 페이지의 메타박스를 확인해 보세요.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="quick-button-settings-form">저장하기</button>
            </div>
        </div>
    </div>
</div>


