<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_adclicker']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_adclicker_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_ad_clicker_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_adclicker_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'ad_clicker' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_ad_clicker_enabled', $enable);
        $updated = true;
    }
}

if (isset($_POST['save_adclicker_settings'])) {
    check_admin_referer('presslearn_save_adclicker_settings_nonce');
    
    if (current_user_can('manage_options')) {
        $adclicker_frequency = isset($_POST['adclicker_frequency']) ? sanitize_text_field(wp_unslash($_POST['adclicker_frequency'])) : 'once';
        $adclicker_overlay_color = isset($_POST['adclicker_overlay_color']) ? sanitize_hex_color(wp_unslash($_POST['adclicker_overlay_color'])) : '#000000';
        $adclicker_overlay_range = isset($_POST['adclicker_overlay_range']) ? intval(wp_unslash($_POST['adclicker_overlay_range'])) : 50;
        $adclicker_display_time = isset($_POST['adclicker_display_time']) ? sanitize_text_field(wp_unslash($_POST['adclicker_display_time'])) : 'null';
        $adclicker_button_color = isset($_POST['adclicker_button_color']) ? sanitize_hex_color(wp_unslash($_POST['adclicker_button_color'])) : '#2196F3';
        $adclicker_button_text_color = isset($_POST['adclicker_button_text_color']) ? sanitize_hex_color(wp_unslash($_POST['adclicker_button_text_color'])) : '#ffffff';
        $adclicker_global_enabled = isset($_POST['adclicker_global_enabled']) ? sanitize_text_field(wp_unslash($_POST['adclicker_global_enabled'])) : 'no';
        
        $adclicker_overlay_range = max(1, min(100, $adclicker_overlay_range));
        
        update_option('presslearn_adclicker_frequency', $adclicker_frequency);
        update_option('presslearn_adclicker_overlay_color', $adclicker_overlay_color);
        update_option('presslearn_adclicker_overlay_range', $adclicker_overlay_range);
        update_option('presslearn_adclicker_display_time', $adclicker_display_time);
        update_option('presslearn_adclicker_button_color', $adclicker_button_color);
        update_option('presslearn_adclicker_button_text_color', $adclicker_button_text_color);
        update_option('presslearn_adclicker_global_enabled', $adclicker_global_enabled);
        
        $settings_updated = true;
    }
}

$adclicker_enabled = get_option('presslearn_ad_clicker_enabled', 'no');
$adclicker_frequency = get_option('presslearn_adclicker_frequency', 'once');
$adclicker_overlay_color = get_option('presslearn_adclicker_overlay_color', '#000000');
$adclicker_overlay_range = get_option('presslearn_adclicker_overlay_range', 100);
$adclicker_display_time = get_option('presslearn_adclicker_display_time', 'null');
$adclicker_button_color = get_option('presslearn_adclicker_button_color', '#2196F3');
$adclicker_button_text_color = get_option('presslearn_adclicker_button_text_color', '#ffffff');
$adclicker_global_enabled = get_option('presslearn_adclicker_global_enabled', 'no');

?>
<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>애드클리커</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($adclicker_enabled === 'yes'): ?>
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
            <span class="active">애드클리커</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>

    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>애드클리커</h2>
            <p>애드클리커는 다양한 방식으로 자체 광고 및 어필리에이트 광고 세팅을 도와줍니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="adclicker-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_adclicker_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_adclicker" value="1">
                        <input type="hidden" name="enable" id="adclicker-enable-value" value="<?php echo esc_attr($adclicker_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($adclicker_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('adclicker-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('adclicker-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>  
            <form method="post" action="" id="adclicker-settings-form">
                <?php wp_nonce_field('presslearn_save_adclicker_settings_nonce'); ?>
                <input type="hidden" name="save_adclicker_settings" value="1">
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>전체 글 강제 활성화 적용 여부</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <label class="switch">
                            <input type="checkbox" name="adclicker_global_enabled" value="yes" <?php echo esc_attr($adclicker_global_enabled === 'yes' ? 'checked' : ''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 빈도 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="adclicker_frequency">
                            <option value="once" <?php selected($adclicker_frequency, 'once'); ?>>세션 당 한 번만</option>
                            <option value="5min" <?php selected($adclicker_frequency, '5min'); ?>>5분 당 한 번만</option>
                            <option value="loop" <?php selected($adclicker_frequency, 'loop'); ?>>반복 표시</option>
                        </select>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 오버레이 색상 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="color" name="adclicker_overlay_color" value="<?php echo esc_attr($adclicker_overlay_color); ?>">
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 오버레이 범위 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <div class="input-with-suffix">
                            <input type="number" name="adclicker_overlay_range" value="<?php echo esc_attr($adclicker_overlay_range); ?>" min="1" max="100" step="1">
                            <span class="input-suffix">%</span>
                        </div>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 표시 시간 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="adclicker_display_time">
                            <option value="null" <?php selected($adclicker_display_time, 'null'); ?>>제한 없음</option>
                            <option value="5" <?php selected($adclicker_display_time, '5'); ?>>5초</option>
                            <option value="10" <?php selected($adclicker_display_time, '10'); ?>>10초</option>
                            <option value="15" <?php selected($adclicker_display_time, '15'); ?>>15초</option>
                        </select>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 버튼 색상 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="color" name="adclicker_button_color" value="<?php echo esc_attr($adclicker_button_color); ?>">
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 버튼 텍스트 색상 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="color" name="adclicker_button_text_color" value="<?php echo esc_attr($adclicker_button_text_color); ?>">
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>애드클리커 미리보기</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <button type="button" id="preview-adclicker" class="secondary-btn">미리보기</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">쿠팡파트너스 정책을 확인해 주세요. 무분별한 광고 유도는 정책 위반입니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="adclicker-settings-form">저장하기</button>
            </div>
        </div>
    </div>
</div>

