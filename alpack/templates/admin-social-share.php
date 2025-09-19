<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_social_share']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_social_share_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_social_share_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_social_share_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'social_share' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_social_share_enabled', $enable);
        $updated = true;
    }
}

if (isset($_POST['save_social_share_settings'])) {
    check_admin_referer('presslearn_save_social_share_settings_nonce');
    
    if (current_user_can('manage_options')) {
        $social_share_options = array();
        if (isset($_POST['social_share_options']) && is_array($_POST['social_share_options'])) {
            $social_share_options = array_map('sanitize_text_field', array_map('wp_unslash', $_POST['social_share_options']));
        }
        
        $valid_options = array();
        $allowed_options = array('facebook', 'naver', 'line', 'kakaotalk', 'band', 'twitter');
        
        foreach ($social_share_options as $option) {
            if (in_array($option, $allowed_options)) {
                $valid_options[] = $option;
            }
        }
        
        update_option('presslearn_social_share_options', $valid_options);
        
        if (isset($_POST['kakao_api_key'])) {
            $kakao_api_key = sanitize_text_field(wp_unslash($_POST['kakao_api_key']));
            update_option('presslearn_kakao_api_key', $kakao_api_key);
        }
        
        if (isset($_POST['social_share_style'])) {
            $style = sanitize_text_field(wp_unslash($_POST['social_share_style']));
            if (in_array($style, array('default', 'popup'))) {
                update_option('presslearn_social_share_style', $style);
            }
        }
        
        if (isset($_POST['social_share_alignment'])) {
            $alignment = sanitize_text_field(wp_unslash($_POST['social_share_alignment']));
            if (in_array($alignment, array('left', 'right', 'center', 'full', 'inline'))) {
                update_option('presslearn_social_share_alignment', $alignment);
            }
        }
        
        $settings_updated = true;
    }
}

$social_share_enabled = get_option('presslearn_social_share_enabled', 'no');
$social_share_options = get_option('presslearn_social_share_options', array('facebook', 'naver', 'line', 'kakaotalk', 'band', 'twitter'));
$kakao_api_key = get_option('presslearn_kakao_api_key', '');
$has_kakao_api_key = !empty($kakao_api_key);
$social_share_style = get_option('presslearn_social_share_style', 'default');
$social_share_alignment = get_option('presslearn_social_share_alignment', 'left');

?>

<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>소셜 공유</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($social_share_enabled === 'yes'): ?>
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
            <span class="active">소셜 공유</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>

    <?php if (!$has_kakao_api_key): ?>
    <div class="notice notice-warning inline">
        <p>카카오톡 공유 기능을 사용하려면 카카오톡 API KEY를 입력해주세요.<br/><a href="https://developers.kakao.com/" target="_blank">카카오톡 API KEY 발급 받기</a></p>
    </div>
    <?php endif; ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>소셜 공유</h2>
            <p>다양한 소셜 미디어 매체에 간편하게 나의 게시물을 공유할 수 있도록 도와줍니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="social-share-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_social_share_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_social_share" value="1">
                        <input type="hidden" name="enable" id="social-share-enable-value" value="<?php echo esc_attr($social_share_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($social_share_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('social-share-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('social-share-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <form method="post" action="" id="social-share-settings-form">
                <?php wp_nonce_field('presslearn_save_social_share_settings_nonce'); ?>
                <input type="hidden" name="save_social_share_settings" value="1">
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>활성화할 소셜 미디어</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <div class="social-share-options">
                            <label class="social-share-button">
                                <input type="checkbox" name="social_share_options[]" value="facebook" <?php echo esc_attr(in_array('facebook', $social_share_options) ? 'checked' : ''); ?>>
                                <span class="social-btn">페이스북</span>
                            </label><label class="social-share-button">
                                <input type="checkbox" name="social_share_options[]" value="band" <?php echo esc_attr(in_array('band', $social_share_options) ? 'checked' : ''); ?>>
                                <span class="social-btn">밴드</span>
                            </label><label class="social-share-button" id="kakaotalk-option-label">
                                <input type="checkbox" name="social_share_options[]" value="kakaotalk" id="kakaotalk-option" <?php echo esc_attr(in_array('kakaotalk', $social_share_options) ? 'checked' : ''); ?> <?php echo esc_attr(!$has_kakao_api_key ? 'disabled' : ''); ?>>
                                <span class="social-btn">카카오톡</span>
                            </label><label class="social-share-button">
                                <input type="checkbox" name="social_share_options[]" value="naver" <?php echo esc_attr(in_array('naver', $social_share_options) ? 'checked' : ''); ?>>
                                <span class="social-btn">네이버</span>
                            </label><label class="social-share-button">
                                <input type="checkbox" name="social_share_options[]" value="line" <?php echo esc_attr(in_array('line', $social_share_options) ? 'checked' : ''); ?>>
                                <span class="social-btn">라인</span>
                            </label><label class="social-share-button">
                                <input type="checkbox" name="social_share_options[]" value="twitter" <?php echo esc_attr(in_array('twitter', $social_share_options) ? 'checked' : ''); ?>>
                                <span class="social-btn">X</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>카카오톡 API KEY</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="text" name="kakao_api_key" class="regular-text" value="<?php echo esc_attr($kakao_api_key); ?>" id="kakao-api-key">
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>소셜 공유 스타일</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="social_share_style" id="social-share-style">
                            <option value="default" <?php selected($social_share_style, 'default'); ?>>나열 형식</option>
                            <option value="popup" <?php selected($social_share_style, 'popup'); ?>>팝업 형식</option>
                        </select>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>소셜 공유 버튼 정렬 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <select name="social_share_alignment" id="social-share-alignment">
                            <option value="left" <?php selected($social_share_alignment, 'left'); ?>>왼쪽</option>
                            <option value="right" <?php selected($social_share_alignment, 'right'); ?>>오른쪽</option>
                            <option value="center" <?php selected($social_share_alignment, 'center'); ?>>가운데</option>
                            <option value="full" <?php selected($social_share_alignment, 'full'); ?> class="popup-only">가로 최대</option>
                            <option value="inline" <?php selected($social_share_alignment, 'inline'); ?>>인라인</option>
                        </select>
                    </div>
                </div>
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>소셜 공유 숏코드 복사</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right" style="display: flex; align-items: center; justify-content: end; gap: 10px;">
                        <textarea name="social_share_shortcode" id="social-share-shortcode" class="regular-text" style="height: 38px;" readonly><?php echo esc_textarea(presslearn_get_social_share_shortcode()); ?></textarea>
                        <button type="button" class="point-btn" id="copy-social-share-shortcode">복사하기</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">카카오톡 디벨로퍼스에서 발급받은 Javascript KEY를 입력해주세요.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="social-share-settings-form">저장하기</button>
            </div>
        </div>
    </div>
</div>
