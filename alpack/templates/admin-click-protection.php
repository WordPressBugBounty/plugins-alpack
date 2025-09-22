<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_click_protection']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_click_protection_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_click_protection_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_click_protection_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'click_protection' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_click_protection_enabled', $enable);
        $updated = true;
    }
}

if (isset($_POST['save_click_protection_settings'])) {
    check_admin_referer('presslearn_save_click_protection_settings_nonce');
    
    if (current_user_can('manage_options')) {
        $max_click_count = isset($_POST['max_click_count']) ? intval(wp_unslash($_POST['max_click_count'])) : 10;
        if ($max_click_count < 1) $max_click_count = 1;
        if ($max_click_count > 100) $max_click_count = 100;
        
        $click_time_window = isset($_POST['click_time_window']) ? intval(wp_unslash($_POST['click_time_window'])) : 30;
        if ($click_time_window < 1) $click_time_window = 1;
        if ($click_time_window > 1440) $click_time_window = 1440;
        
        $use_cloudflare = isset($_POST['use_cloudflare']) ? 'yes' : 'no';
        
        $block_expiry_days = isset($_POST['block_expiry_days']) ? intval(wp_unslash($_POST['block_expiry_days'])) : 30;
        if ($block_expiry_days < 1) $block_expiry_days = 1;
        if ($block_expiry_days > 365) $block_expiry_days = 365;
        
        $modal_title = isset($_POST['modal_title']) ? sanitize_text_field(wp_unslash($_POST['modal_title'])) : '광고 차단 알림';
        
        $modal_message = isset($_POST['modal_message']) ? sanitize_text_field(wp_unslash($_POST['modal_message'])) : '광고 클릭 제한을 초과하여 광고가 차단되었습니다.';
        
        $modal_submessage = isset($_POST['modal_submessage']) ? sanitize_text_field(wp_unslash($_POST['modal_submessage'])) : '단시간에 반복적인 광고 클릭은 시스템에 의해 감지되며, IP가 수집되어 사이트 관리자가 확인 가능합니다.';
        
        $modal_button_text = isset($_POST['modal_button_text']) ? sanitize_text_field(wp_unslash($_POST['modal_button_text'])) : '확인';
        
        update_option('presslearn_max_click_count', $max_click_count);
        update_option('presslearn_click_time_window', $click_time_window);
        update_option('presslearn_click_protection_use_cloudflare', $use_cloudflare);
        update_option('presslearn_click_protection_block_expiry_days', $block_expiry_days);
        update_option('presslearn_modal_title', $modal_title);
        update_option('presslearn_modal_message', $modal_message);
        update_option('presslearn_modal_submessage', $modal_submessage);
        update_option('presslearn_modal_button_text', $modal_button_text);
        
        $settings_updated = true;
    }
}

$click_protection_enabled = get_option('presslearn_click_protection_enabled', 'no');
$max_click_count = get_option('presslearn_max_click_count', 10);
$click_time_window = get_option('presslearn_click_time_window', 30);
$use_cloudflare = get_option('presslearn_click_protection_use_cloudflare', 'no');
$block_expiry_days = get_option('presslearn_click_protection_block_expiry_days', 30);

$modal_title = get_option('presslearn_modal_title', '광고 차단 알림');
$modal_message = get_option('presslearn_modal_message', '광고 클릭 제한을 초과하여 광고가 차단되었습니다.');
$modal_submessage = get_option('presslearn_modal_submessage', '단시간에 반복적인 광고 클릭은 시스템에 의해 감지되며, IP가 수집되어 사이트 관리자가 확인 가능합니다.');
$modal_button_text = get_option('presslearn_modal_button_text', '확인');

?>
<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>애드 프로텍터</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($click_protection_enabled === 'yes'): ?>
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
            <span class="active">애드 프로텍터</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>
    

    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>애드 프로텍터</h2>
            <p>구글 애드센스 광고를 악의적인 공격으로부터 보호할 수 있는 기능입니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="click-protection-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_click_protection_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_click_protection" value="1">
                        <input type="hidden" name="enable" id="click-protection-enable-value" value="<?php echo esc_attr($click_protection_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($click_protection_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('click-protection-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('click-protection-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            
            <form method="post" action="" id="click-protection-settings-form">
                <?php wp_nonce_field('presslearn_save_click_protection_settings_nonce'); ?>
                <input type="hidden" name="save_click_protection_settings" value="1">
                <input type="hidden" name="modal_title" value="<?php echo esc_attr($modal_title); ?>">
                <input type="hidden" name="modal_message" value="<?php echo esc_attr($modal_message); ?>">
                <input type="hidden" name="modal_submessage" value="<?php echo esc_attr($modal_submessage); ?>">
                <input type="hidden" name="modal_button_text" value="<?php echo esc_attr($modal_button_text); ?>">
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>최대 허용 클릭 수</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="number" class="regular-text" name="max_click_count" value="<?php echo esc_attr($max_click_count); ?>" min="1" max="100">
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>클릭 감지 시간(분)</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="number" class="regular-text" name="click_time_window" value="<?php echo esc_attr($click_time_window); ?>" min="1" max="1440">
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>CloudFlare 사용</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <label class="switch">
                            <input type="checkbox" name="use_cloudflare" <?php echo esc_attr($use_cloudflare === 'yes' ? 'checked' : ''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>허용 IP 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <button type="button" id="open-allowed-ip-editor" class="point-btn">설정하기</button>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>차단 IP 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <button type="button" id="open-blocked-ip-editor" class="point-btn">설정하기</button>
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>차단 IP 해제 주기(일)</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="number" class="regular-text" name="block_expiry_days" value="<?php echo esc_attr($block_expiry_days); ?>" min="1" max="365">
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>차단 모달 설정</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <button type="button" id="open-modal-settings" class="point-btn">설정하기</button>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>차단 로그 확인</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <button type="button" id="open-blocked-log-modal" class="point-btn">확인하기</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">애드 프로텍터는 광고 차단의 무결성을 보장하지 않습니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="click-protection-settings-form">저장하기</button>
            </div>
        </div>
    </div>
</div> 

<div id="allowed-ip-modal" class="presslearn-modal" style="display: none;">
    <div class="presslearn-modal-content">
        <div class="presslearn-modal-header">
            <h3>허용 IP 설정</h3>
            <span class="presslearn-modal-close">&times;</span>
        </div>
        <div class="presslearn-modal-body">
            <div class="ip-add-form">
                <div class="ip-input-group">
                    <input type="text" id="new-ip-address" placeholder="IP 주소 입력 (예: 192.168.0.1)" class="regular-text">
                    <button type="button" id="add-ip-button" class="point-btn">추가</button>
                </div>
                <p class="ip-description">추가한 IP는 클릭 제한에서 제외됩니다.</p>
            </div>
            
            <div class="ip-list-container">
                <table class="widefat ip-list-table">
                    <thead>
                        <tr>
                            <th>IP 주소</th>
                            <th>추가 날짜</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody id="allowed-ip-list">
                    </tbody>
                </table>
                <div id="no-ips-message" style="text-align: center; padding: 20px; display: none;">
                    <p>등록된 IP가 없습니다. IP를 추가하여 클릭 제한에서 제외하세요.</p>
                </div>
            </div>
        </div>
        <div class="presslearn-modal-footer">
            <button type="button" id="close-modal-button" class="secondary-btn">닫기</button>
        </div>
    </div>
</div>

<div id="blocked-ip-modal" class="presslearn-modal" style="display: none;">
    <div class="presslearn-modal-content">
        <div class="presslearn-modal-header">
            <h3>차단 IP 설정</h3>
            <span class="presslearn-modal-close">&times;</span>
        </div>
        <div class="presslearn-modal-body">
            <div class="ip-add-form">
                <div class="ip-input-group">
                    <input type="text" id="new-blocked-ip" placeholder="차단할 IP 주소 입력 (예: 192.168.0.1)" class="regular-text">
                    <button type="button" id="add-blocked-ip-button" class="point-btn">추가</button>
                </div>
                <div style="margin: 10px 0;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="permanent-block-checkbox">
                        <span>영구 차단 (만료일 없음)</span>
                    </label>
                </div>
                <p class="ip-description">추가한 IP는 광고 클릭이 완전히 차단됩니다.</p>
            </div>
            
            <div class="ip-list-container">
                <table class="widefat ip-list-table">
                    <thead>
                        <tr>
                            <th>IP 주소</th>
                            <th>차단 날짜</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody id="blocked-ip-list">
                    </tbody>
                </table>
                <div id="no-blocked-ips-message" style="text-align: center; padding: 20px; display: none;">
                    <p>등록된 차단 IP가 없습니다. 악의적인 클릭이 의심되는 IP를 추가하세요.</p>
                </div>
            </div>
        </div>
        <div class="presslearn-modal-footer">
            <button type="button" id="close-blocked-modal-button" class="secondary-btn">닫기</button>
        </div>
    </div>
</div>

<div id="modal-settings-editor" class="presslearn-modal" style="display: none;">
    <div class="presslearn-modal-content">
        <div class="presslearn-modal-header">
            <h3>차단 모달 설정</h3>
            <span class="presslearn-modal-close">&times;</span>
        </div>
        <div class="presslearn-modal-body">
            <form id="modal-settings-form">
                <div class="settings-form-row">
                    <label for="modal_title">모달 제목</label>
                    <input type="text" id="modal_title" name="modal_title" value="<?php echo esc_attr($modal_title); ?>" class="regular-text">
                </div>
                
                <div class="settings-form-row">
                    <label for="modal_message">모달 메시지</label>
                    <input type="text" id="modal_message" name="modal_message" value="<?php echo esc_attr($modal_message); ?>" class="regular-text">
                </div>
                
                <div class="settings-form-row">
                    <label for="modal_submessage">모달 서브 메시지</label>
                    <input type="text" id="modal_submessage" name="modal_submessage" value="<?php echo esc_attr($modal_submessage); ?>" class="regular-text">
                </div>
                
                <div class="settings-form-row">
                    <label for="modal_button_text">모달 버튼 텍스트</label>
                    <input type="text" id="modal_button_text" name="modal_button_text" value="<?php echo esc_attr($modal_button_text); ?>" class="regular-text">
                </div>
            </form>
            
            <div class="modal-preview">
                <h4>미리보기</h4>
                <div class="modal-preview-container">
                    <h3 style="color: #d32f2f; margin-top: 0;" id="preview-title"><?php echo esc_html($modal_title); ?></h3>
                    <p style="margin: 10px 0;" id="preview-message"><?php echo esc_html($modal_message); ?></p>
                    <p style="margin: 10px 0; font-size: 13px;" id="preview-submessage"><?php echo esc_html($modal_submessage); ?></p>
                    <button style="background: #d32f2f; color: white; border: none; padding: 8px 16px; border-radius: 4px; margin-top: 10px; cursor: default;" id="preview-button"><?php echo esc_html($modal_button_text); ?></button>
                </div>
            </div>
        </div>
        <div class="presslearn-modal-footer">
            <button type="button" id="save-modal-settings" class="point-btn">저장</button>
            <button type="button" id="close-modal-settings" class="secondary-btn">취소</button>
        </div>
    </div>
</div>

<div id="blocked-log-modal" class="presslearn-modal" style="display: none;">
    <div class="presslearn-modal-content">
        <div class="presslearn-modal-header">
            <h3>차단 로그 확인</h3>
            <span class="presslearn-modal-close">&times;</span>
        </div>
        <div class="presslearn-modal-body">
            <div class="log-filter">
                <select id="log-filter-period" class="regular-text">
                    <option value="7">최근 7일</option>
                    <option value="30" selected>최근 30일</option>
                    <option value="90">최근 90일</option>
                    <option value="all">전체 기간</option>
                </select>
                <button type="button" id="refresh-log" class="point-btn">새로고침</button>
            </div>
            
            <div class="log-list-container">
                <table class="widefat log-list-table">
                    <thead>
                        <tr>
                            <th>IP 주소</th>
                            <th>차단 날짜</th>
                            <th>차단 사유</th>
                            <th>만료 예정일</th>
                            <th>작업</th>
                        </tr>
                    </thead>
                    <tbody id="blocked-log-list">
                    </tbody>
                </table>
                <div id="no-blocked-logs-message" style="text-align: center; padding: 20px; display: none;">
                    <p>차단 로그가 없습니다.</p>
                </div>
                <div id="log-loading" style="text-align: center; padding: 20px; display: none;">
                    <div class="spinner-container">
                        <span class="spinner is-active"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="presslearn-modal-footer">
            <button type="button" id="close-log-modal-button" class="secondary-btn">닫기</button>
        </div>
    </div>
</div>

