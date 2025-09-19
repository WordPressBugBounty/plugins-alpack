<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_dynamic_banner']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_dynamic_banner_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_dynamic_banner_enabled', $enable);
            $updated = true;
        }
    }
}

if (current_user_can('manage_options') && is_admin() && isset($_GET['action']) && isset($_GET['enable']) && isset($_GET['feature'])) {
    if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'presslearn_toggle_dynamic_banner_action')) {
        wp_die('보안 검증에 실패했습니다.');
    }
    
    $action = sanitize_text_field(wp_unslash($_GET['action']));
    $enable = sanitize_text_field(wp_unslash($_GET['enable']));
    $feature = sanitize_text_field(wp_unslash($_GET['feature']));
    
    if ($action === 'toggle' && $feature === 'dynamic_banner' && ($enable === 'yes' || $enable === 'no')) {
        update_option('presslearn_dynamic_banner_enabled', $enable);
        $updated = true;
    }
}

$dynamic_banner_enabled = get_option('presslearn_dynamic_banner_enabled', 'no');

wp_enqueue_media();

?>
<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>다이나믹 배너</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($dynamic_banner_enabled === 'yes'): ?>
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
            <span class="active">다이나믹 배너</span>
        </div>
    </div>

    <?php if (isset($updated) && $updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>다이나믹 배너</h2>
            <p>다이나믹 배너는 다양한 방식으로 자체 광고 및 어필리에이트 광고 세팅을 도와줍니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="dynamic-banner-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_dynamic_banner_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_dynamic_banner" value="1">
                        <input type="hidden" name="enable" id="dynamic-banner-enable-value" value="<?php echo esc_attr($dynamic_banner_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($dynamic_banner_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('dynamic-banner-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('dynamic-banner-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>다이나믹 배너 설정</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <button type="button" class="secondary-btn" id="open-shortcode-modal">숏 코드 관리</button>
                </div>  
            </div>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">배너 별 숏 코드를 관리할 수 있습니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="dynamic-banner-settings-form">저장하기</button>
            </div>
        </div>
    </div>

    <div id="shortcode-modal" class="presslearn-modal" style="display: none;">
        <div class="presslearn-modal-content">
            <div class="presslearn-modal-header">
                <h3>다이나믹 배너 캠페인 관리</h3>
                <span class="presslearn-modal-close">&times;</span>
            </div>
            <div class="presslearn-modal-body">
                <div class="campaign-tabs">
                    <button class="campaign-tab active" data-tab="campaigns">캠페인 목록</button>
                    <button class="campaign-tab" data-tab="add-campaign">새 캠페인 추가</button>
                </div>
                
                <div class="campaign-tab-content active" id="campaigns-tab">
                    <table class="widefat campaign-table">
                        <thead>
                            <tr>
                                <th>캠페인 이름</th>
                                <th>숏코드</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody id="campaign-list">
                            <tr>
                                <td colspan="4" class="no-campaigns">등록된 캠페인이 없습니다. 새 캠페인을 추가해주세요.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="campaign-tab-content" id="add-campaign-tab">
                    <form id="add-campaign-form">
                        <div class="settings-form-row">
                            <label for="campaign_name">캠페인 이름</label>
                            <input type="text" id="campaign_name" name="campaign_name" class="regular-input-form" placeholder="캠페인 이름을 입력하세요">
                        </div>
                        
                        <div class="settings-form-row">
                            <label for="banner_type">배너 유형</label>
                            <div class="banner-type-selector">
                                <label class="banner-type-option">
                                    <input type="radio" name="banner_type" value="custom" checked>
                                    <div class="banner-type-btn">자체광고</div>
                                </label>
                                <label class="banner-type-option">
                                    <input type="radio" name="banner_type" value="iframe">
                                    <div class="banner-type-btn">iframe 방식</div>
                                </label>
                            </div>
                        </div>
                        
                        <div id="custom-banner-fields">
                            <div class="settings-form-row">
                                <label for="campaign_banner_url">배너 이미지</label>
                                <div class="banner-upload-container">
                                    <input type="text" id="campaign_banner_url" name="campaign_banner_url" class="regular-input-form" placeholder="이미지를 업로드하세요" readonly>
                                    <button type="button" id="upload-banner" class="secondary-btn">이미지 선택</button>
                                </div>
                                <div id="banner-preview" class="banner-preview" style="display: none;">
                                    <img src="" alt="배너 미리보기">
                                </div>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="banner_image_file" accept="image/*" class="file-upload-input" style="display: none;">
                                    <div class="file-upload-info" style="display: none;">
                                        <span class="file-name"></span>
                                        <div class="file-upload-progress">
                                            <div class="progress-bar"></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="form-hint">이미지 선택 버튼을 클릭하여 배너 이미지를 업로드하세요.</p>
                            </div>

                            <div class="settings-form-row">
                                <label for="campaign_cover_banner_url">커버 배너 이미지</label>
                                <div class="banner-upload-container">
                                    <input type="text" id="campaign_cover_banner_url" name="campaign_cover_banner_url" class="regular-input-form" placeholder="이미지를 업로드하세요" readonly>
                                    <button type="button" id="upload-cover-banner" class="secondary-btn">이미지 선택</button>
                                </div>
                                <div id="cover-banner-preview" class="banner-preview" style="display: none;">
                                    <img src="" alt="커버 배너 미리보기">
                                </div>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="cover_banner_image_file" accept="image/*" class="file-upload-input" style="display: none;">
                                    <div class="file-upload-info" style="display: none;">
                                        <span class="file-name"></span>
                                        <div class="file-upload-progress">
                                            <div class="progress-bar"></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="form-hint">이미지 선택 버튼을 클릭하여 커버 배너 이미지를 업로드하세요.</p>
                            </div>
                            
                            <div class="settings-form-row banner-dimensions">
                                <div class="dimension-field">
                                    <label for="banner_width">가로 너비 <span class="required">*</span></label>
                                    <div class="input-with-suffix" style="display: flex;">
                                        <input type="number" id="banner_width" name="banner_width" class="regular-input-form" placeholder="가로 너비" min="1" max="2000" required>
                                        <span class="input-suffix">px</span>
                                    </div>
                                </div>
                                <div class="dimension-field">
                                    <label for="banner_height">세로 높이 <span class="required">*</span></label>
                                    <div class="input-with-suffix" style="display: flex;">
                                        <input type="number" id="banner_height" name="banner_height" class="regular-input-form" placeholder="세로 높이" min="1" max="2000" required>
                                        <span class="input-suffix">px</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="settings-form-row">
                                <label for="campaign_link">링크 URL</label>
                                <input type="url" id="campaign_link" name="campaign_link" class="regular-input-form" placeholder="배너 클릭 시 이동할 URL">
                            </div>
                        </div>
                        
                        <div id="iframe-banner-fields" style="display: none;">
                            <div class="settings-form-row">
                                <label for="iframe_code">iframe 코드</label>
                                <div class="code-editor-container">
                                    <div class="code-editor-header">
                                        <span>HTML</span>
                                    </div>
                                    <textarea id="iframe_code" name="iframe_code" class="code-editor" rows="8" placeholder="<iframe src=&quot;https://example.com/ad&quot; width=&quot;300&quot; height=&quot;250&quot;></iframe>"></textarea>
                                </div>
                                <p class="form-hint">알리 익스프레스, 쿠팡 파트너스 등의 광고 코드를 붙여넣으세요.</p>
                            </div>

                            <div class="settings-form-row">
                                <label for="iframe_link_url">이동 URL (선택사항)</label>
                                <input type="url" id="iframe_link_url" name="iframe_link_url" class="regular-input-form" placeholder="배너 클릭 시 이동할 URL (예: https://example.com)">
                                <p class="form-hint">iframe 클릭 시 이동할 URL을 입력하세요. 입력하지 않으면 iframe의 src 주소로 이동합니다.</p>
                            </div>

                            <div class="settings-form-row">
                                <label for="iframe_campaign_cover_banner_url">커버 배너 이미지</label>
                                <div class="banner-upload-container">
                                    <input type="text" id="iframe_campaign_cover_banner_url" name="iframe_campaign_cover_banner_url" class="regular-input-form" placeholder="이미지를 업로드하세요" readonly>
                                    <button type="button" id="iframe-upload-cover-banner" class="secondary-btn">이미지 선택</button>
                                </div>
                                <div id="iframe-cover-banner-preview" class="banner-preview" style="display: none;">
                                    <img src="" alt="커버 배너 미리보기">
                                </div>
                                <div class="file-upload-wrapper">
                                    <input type="file" id="iframe_cover_banner_image_file" accept="image/*" class="file-upload-input" style="display: none;">
                                    <div class="file-upload-info" style="display: none;">
                                        <span class="file-name"></span>
                                        <div class="file-upload-progress">
                                            <div class="progress-bar"></div>
                                        </div>
                                    </div>
                                </div>
                                <p class="form-hint">이미지 선택 버튼을 클릭하여 커버 배너 이미지를 업로드하세요.</p>
                            </div>
                            
                            <div class="settings-form-row banner-dimensions">
                                <div class="dimension-field">
                                    <label for="iframe_width">가로 너비 <span class="required">*</span></label>
                                    <div class="input-with-suffix" style="display: flex;">
                                        <input type="number" id="iframe_width" name="iframe_width" class="regular-input-form" placeholder="가로 너비" min="1" max="2000">
                                        <span class="input-suffix">px</span>
                                    </div>
                                </div>
                                <div class="dimension-field">
                                    <label for="iframe_height">세로 높이 <span class="required">*</span></label>
                                    <div class="input-with-suffix" style="display: flex;">
                                        <input type="number" id="iframe_height" name="iframe_height" class="regular-input-form" placeholder="세로 높이" min="1" max="2000">
                                        <span class="input-suffix">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="point-btn">캠페인 추가</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="presslearn-modal-footer">
                <button type="button" id="close-modal-button" class="secondary-btn">닫기</button>
            </div>
        </div>
    </div>
    
</div>