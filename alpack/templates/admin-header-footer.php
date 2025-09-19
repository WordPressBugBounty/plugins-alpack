<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_header_footer']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_header_footer_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_header_footer_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_header_footer_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'header_footer' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_header_footer_enabled', $enable);
        $updated = true;
    }
}

if (isset($_POST['save_header_footer_settings'])) {
    check_admin_referer('presslearn_save_header_footer_nonce');
    
    if (current_user_can('manage_options')) {
        $max_length = 65535;
        
        $header = isset($_POST['header_code']) ? wp_unslash($_POST['header_code']) : '';
        if (strlen($header) <= $max_length) {
            update_option('presslearn_header_code', $header);
        }
        
        $footer = isset($_POST['footer_code']) ? wp_unslash($_POST['footer_code']) : '';
        if (strlen($footer) <= $max_length) {
            update_option('presslearn_footer_code', $footer);
        }
        
        $body_open = isset($_POST['body_open_code']) ? wp_unslash($_POST['body_open_code']) : '';
        if (strlen($body_open) <= $max_length) {
            update_option('presslearn_body_open_code', $body_open);
        }
        
        $before_closing = isset($_POST['before_closing_body_code']) ? wp_unslash($_POST['before_closing_body_code']) : '';
        if (strlen($before_closing) <= $max_length) {
            update_option('presslearn_before_closing_body_code', $before_closing);
        }
        
        $settings_updated = true;
    } else {
        $save_error = true;
    }
}

$header_footer_enabled = get_option('presslearn_header_footer_enabled', 'no');
$header_code = get_option('presslearn_header_code', '');
$footer_code = get_option('presslearn_footer_code', '');
$body_open_code = get_option('presslearn_body_open_code', '');
$before_closing_body_code = get_option('presslearn_before_closing_body_code', '');

?>

<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>헤더 & 푸터</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($header_footer_enabled === 'yes'): ?>
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
            <span class="active">헤더 & 푸터</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>

    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>헤더 & 푸터</h2>
            <p>웹사이트의 헤더와 푸터에 HTML, CSS, JavaScript 코드를 삽입할 수 있습니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="header-footer-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_header_footer_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_header_footer" value="1">
                        <input type="hidden" name="enable" id="header-footer-enable-value" value="<?php echo esc_attr($header_footer_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($header_footer_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('header-footer-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('header-footer-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <form method="post" action="" id="header-footer-settings-form">
                <?php wp_nonce_field('presslearn_save_header_footer_nonce'); ?>
                <input type="hidden" name="save_header_footer_settings" value="1">
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>헤더 코드 (HTML Head)</h3>
                        <p>웹사이트의 &lt;head&gt; 영역에 삽입할 코드를 입력하세요.</p>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <textarea name="header_code" rows="8" cols="50" class="widefat" placeholder="원하는 코드를 입력해 주세요"><?php echo esc_textarea($header_code); ?></textarea>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>Body 시작 코드</h3>
                        <p>&lt;body&gt; 태그 바로 다음에 삽입할 코드를 입력하세요.</p>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <textarea name="body_open_code" rows="8" cols="50" class="widefat" placeholder="원하는 코드를 입력해 주세요"><?php echo esc_textarea($body_open_code); ?></textarea>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>Body 종료 전 코드</h3>
                        <p>&lt;/body&gt; 태그 바로 전에 삽입할 코드를 입력하세요.</p>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <textarea name="before_closing_body_code" rows="8" cols="50" class="widefat" placeholder="원하는 코드를 입력해 주세요"><?php echo esc_textarea($before_closing_body_code); ?></textarea>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>푸터 코드</h3>
                        <p>푸터 영역에 삽입할 코드를 입력하세요.</p>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <textarea name="footer_code" rows="8" cols="50" class="widefat" placeholder="원하는 코드를 입력해 주세요"><?php echo esc_textarea($footer_code); ?></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">코드 저장 후 웹사이트에서 즉시 반영됩니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="header-footer-settings-form">저장하기</button>
            </div>
        </div>
    </div>
</div>