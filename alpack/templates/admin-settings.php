<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();
$kakao_login_url = presslearn_plugin()->get_kakao_login_url();

$ai_contents_enabled = get_option('presslearn_ai_contents_enabled', 'no');
$scroll_depth_enabled = get_option('presslearn_scroll_depth_enabled', 'no');
$analytics_enabled = get_option('presslearn_analytics_enabled', 'no');
$click_protection_enabled = get_option('presslearn_click_protection_enabled', 'no');
$ad_clicker_enabled = get_option('presslearn_ad_clicker_enabled', 'no');
$dynamic_banner_enabled = get_option('presslearn_dynamic_banner_enabled', 'no');
$social_share_enabled = get_option('presslearn_social_share_enabled', 'no');
$quick_button_enabled = get_option('presslearn_quick_button_enabled', 'no');
$auto_index_enabled = get_option('presslearn_auto_index_enabled', 'no');
$header_footer_enabled = get_option('presslearn_header_footer_enabled', 'no');
?>
    <div class="presslearn-header">
        <div class="presslearn-header-logo">
            <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
            <h1>AL Pack 플러그인 설정</h1>
        </div>
        <div class="presslearn-header-status">
            <?php if ($is_activated): ?>
            <div class="presslearn-header-status-item status-activate">
                <p>플러그인이 활성화되었습니다.</p>
            </div>
            <?php else: ?>
            <div class="presslearn-header-status-item status-deactivate">
                <p>플러그인이 비활성화 상태입니다.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div id="presslearn-banner-container" class="presslearn-banner-container">
        <div id="presslearn-banner" class="presslearn-banner">
            <a href="#" id="presslearn-banner-link" target="_blank" class="presslearn-banner-link">
                <img src="" id="presslearn-banner-image" class="presslearn-banner-image">
            </a>
        </div>
        <div id="presslearn-banner-skeleton" class="presslearn-banner-skeleton">
            <div class="presslearn-banner-skeleton-content"></div>
        </div>
    </div>

    <div class="wrap">
        <?php 
        $show_updated_notice = false;
        if (current_user_can('manage_options') && is_admin() && isset($_GET['updated'])) {
            $updated_value = sanitize_text_field(wp_unslash($_GET['updated']));
            if ($updated_value === 'true') {
                $show_updated_notice = true;
            }
        }
        ?>
        <?php if ($show_updated_notice): ?>
            <div class="notice notice-success is-dismissible">
                <p>설정이 성공적으로 저장되었습니다.</p>
            </div>
        <?php endif; ?>

        <?php if ($is_activated): ?>
        <?php else: ?>
        <div class="card">
            <h2>플러그인 활성화 상태</h2>
            
            <div class="presslearn-status-section">
                <?php 
                $permalink_structure = get_option('permalink_structure');
                $is_default_permalink = empty($permalink_structure);
                ?>
                
                <?php if ($is_default_permalink): ?>
                <div class="notice notice-error inline">
                    <p><strong>⚠️ 고유주소 설정을 수정해 주세요</strong></p>
                    <p>현재 고유주소 설정이 기본 설정으로 되어 있어 안전한 인증이 되지 않습니다. 다음 단계를 따라 고유주소 설정을 변경해주세요.</p>
                    <ol style="margin-left: 20px;">
                        <li>워드프레스 관리자 → <strong>설정</strong> → <strong>고유주소</strong>로 이동</li>
                        <li><strong>기본형</strong> 대신 <strong>포스트명</strong> 또는 다른 옵션을 선택</li>
                        <li><strong>변경사항 저장</strong> 버튼 클릭</li>
                        <li>이 페이지로 다시 돌아와서 인증 진행</li>
                    </ol>
                    <p>
                        <a href="<?php echo esc_url(admin_url('options-permalink.php')); ?>" class="button button-secondary">
                            고유주소 설정 페이지 열기
                        </a>
                    </p>
                </div>
                
                <p>
                    <button type="button" class="button button-primary kakao-login-button" disabled style="opacity: 0.5;">
                        카카오톡으로 로그인하기
                    </button>
                </p>
                <p class="description">
                    <small>* 고유주소 설정을 변경한 후 인증이 가능합니다.</small>
                </p>
                <?php else: ?>
                <div class="notice notice-warning inline">
                    <p>⚠️ 플러그인이 활성화되지 않았습니다. 아래 버튼을 클릭하여 프레스런 계정으로 로그인하세요.</p>
                </div>
                
                <p>
                    <a href="<?php echo esc_url($kakao_login_url); ?>" class="button button-primary kakao-login-button" id="presslearn-login-button">
                        카카오톡으로 로그인하기
                    </a>
                </p>
                <p class="description">
                    <small>* 프레스런 로그인이 되어있다면, 로그아웃 후 로그인 버튼을 클릭해주세요.</small>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
            
        <div class="presslearn-activated-content"<?php if (!$is_activated): ?> style="display:none"<?php endif; ?>>
            <?php if ($analytics_enabled === 'yes'): ?>
            <div class="row-title">
                <p>최근 7일 통계</p>
            </div>
            <div class="pl-analytics-highlight">
                <div class="pl-analytics-highlight-header">
                    <h2>씬 애널리틱스 하이라이트 <span class="highlight-period">최근 7일</span></h2>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-analytics&tab=statistics')); ?>" class="view-stats-link">전체 통계 보기</a>
                </div>
                
                <?php 
                function alpack_get_week_stats() {
                    global $wpdb;
                    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
                    
                    $stats = [
                        'pageviews' => 0,
                        'visitors' => 0,
                        'ip_visitors' => 0,
                        'prev_pageviews' => 0,
                        'prev_visitors' => 0,
                        'prev_ip_visitors' => 0
                    ];
                    
                    if($wpdb->get_var("SHOW TABLES LIKE '$table_pageviews'") == $table_pageviews) {
                        $current_week_condition = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                        
                        $pageviews = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM $table_pageviews 
                            $current_week_condition
                        ");
                        
                        $visitors = $wpdb->get_var("
                            SELECT COUNT(DISTINCT visitor_id) 
                            FROM $table_pageviews 
                            $current_week_condition
                        ");
                        
                        $ip_visitors = $wpdb->get_var("
                            SELECT COUNT(DISTINCT ip) 
                            FROM $table_pageviews 
                            $current_week_condition
                        ");
                        
                        $prev_week_condition = "WHERE created_at BETWEEN DATE_SUB(CURDATE(), INTERVAL 14 DAY) AND DATE_SUB(CURDATE(), INTERVAL 8 DAY)";
                        
                        $prev_pageviews = $wpdb->get_var("
                            SELECT COUNT(*) 
                            FROM $table_pageviews 
                            $prev_week_condition
                        ");
                        
                        $prev_visitors = $wpdb->get_var("
                            SELECT COUNT(DISTINCT visitor_id) 
                            FROM $table_pageviews 
                            $prev_week_condition
                        ");
                        
                        $prev_ip_visitors = $wpdb->get_var("
                            SELECT COUNT(DISTINCT ip) 
                            FROM $table_pageviews 
                            $prev_week_condition
                        ");
                        
                        $stats['pageviews'] = $pageviews ? intval($pageviews) : 0;
                        $stats['visitors'] = $visitors ? intval($visitors) : 0;
                        $stats['ip_visitors'] = $ip_visitors ? intval($ip_visitors) : 0;
                        $stats['prev_pageviews'] = $prev_pageviews ? intval($prev_pageviews) : 0;
                        $stats['prev_visitors'] = $prev_visitors ? intval($prev_visitors) : 0;
                        $stats['prev_ip_visitors'] = $prev_ip_visitors ? intval($prev_ip_visitors) : 0;
                    }
                    
                    return $stats;
                }
                
                $week_stats = alpack_get_week_stats();
                
                function alpack_calculate_change($current, $previous) {
                    if ($previous == 0) {
                        return [
                            'percentage' => 0,
                            'show' => false
                        ];
                    }
                    
                    $change = $current - $previous;
                    $percentage = round(($change / $previous) * 100);
                    
                    return [
                        'change' => $change,
                        'percentage' => $percentage,
                        'show' => true,
                        'is_increase' => $change > 0
                    ];
                }
                
                $pageviews_change = alpack_calculate_change($week_stats['pageviews'], $week_stats['prev_pageviews']);
                $visitors_change = alpack_calculate_change($week_stats['visitors'], $week_stats['prev_visitors']);
                $ip_visitors_change = alpack_calculate_change($week_stats['ip_visitors'], $week_stats['prev_ip_visitors']);
                ?>
                
                <div class="pl-analytics-highlight-stats">
                    <div class="pl-analytics-highlight-stat">
                        <span class="stat-label">조회</span>
                        <span class="stat-value"><?php echo esc_html(number_format($week_stats['pageviews'])); ?></span>
                        <?php if ($pageviews_change['show']): ?>
                        <span class="stat-change <?php echo esc_attr($pageviews_change['is_increase'] ? 'increase' : 'decrease'); ?>">
                            <?php echo esc_html($pageviews_change['is_increase'] ? '▲' : '▼'); ?> 
                            <?php echo esc_html(number_format(abs($pageviews_change['change']))); ?>
                            (<?php echo esc_html(abs($pageviews_change['percentage'])); ?>%)
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="pl-analytics-highlight-stat">
                        <span class="stat-label">방문자 (IP 기준)</span>
                        <span class="stat-value"><?php echo esc_html(number_format($week_stats['ip_visitors'])); ?></span>
                        <?php if ($ip_visitors_change['show']): ?>
                        <span class="stat-change <?php echo esc_attr($ip_visitors_change['is_increase'] ? 'increase' : 'decrease'); ?>">
                            <?php echo esc_html($ip_visitors_change['is_increase'] ? '▲' : '▼'); ?> 
                            <?php echo esc_html(number_format(abs($ip_visitors_change['change']))); ?>
                            (<?php echo esc_html(abs($ip_visitors_change['percentage'])); ?>%)
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="pl-analytics-highlight-stat">
                        <span class="stat-label">방문자 (브라우저 기준)</span>
                        <span class="stat-value"><?php echo esc_html(number_format($week_stats['visitors'])); ?></span>
                        <?php if ($visitors_change['show']): ?>
                        <span class="stat-change <?php echo esc_attr($visitors_change['is_increase'] ? 'increase' : 'decrease'); ?>">
                            <?php echo esc_html($visitors_change['is_increase'] ? '▲' : '▼'); ?> 
                            <?php echo esc_html(number_format(abs($visitors_change['change']))); ?>
                            (<?php echo esc_html(abs($visitors_change['percentage'])); ?>%)
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="row-title">
                <p>플러그인 설정</p>
            </div>
            <div class="pl-grid">
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge inactive">비활성화됨</span></p>
                        <h3>AI 블로그 포스팅</h3>
                        <p>Gemini Flash 모델을 활용한 콘텐츠 자동 생성 기능입니다. 여러가지 콘텐츠를 생성하실 수 있습니다. *해당 기능은 프레스런 통합 강의 수강생만 사용 가능합니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <button type="button" class="restricted-btn" title="프레스런 통합 강의 수강생 전용 기능" aria-label="사용 제한: 프레스런 통합 강의 수강생 전용 기능" disabled>사용 제한</button>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($scroll_depth_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($scroll_depth_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>스마트 스크롤</h3>
                        <p>스크롤에 따라서 자동으로 팝업을 띄워줄 수 있는 기능입니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-scroll-depth')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($analytics_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($analytics_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>씬 애널리틱스</h3>
                        <p>페이지 별 애널리틱스 기능이 포함되어 있는 경량 애널리틱스 기능입니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-analytics&tab=statistics')); ?>" class="point-btn">통계</a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-analytics&tab=settings')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($click_protection_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($click_protection_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>애드 프로텍터</h3>
                        <p>구글 애드센스 광고를 악의적인 공격으로부터 어느정도 보호할 수 있는 플러그인입니다. 다양한 방식으로 차단 및 관리가 가능합니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-click-protection')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($ad_clicker_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($ad_clicker_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>애드클리커</h3>
                        <p>다양한 방식으로 자체 광고 및 어필리에이트 광고 세팅을 도와줍니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-ad-clicker')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($dynamic_banner_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($dynamic_banner_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>다이나믹 배너</h3>
                        <p>다양한 방식으로 자체 광고 및 어필리에이트 광고 세팅을 도와줍니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-dynamic-banner')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($social_share_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($social_share_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>소셜 공유</h3>
                        <p>다양한 소셜 미디어 매체에 간편하게 나의 게시물을 공유할 수 있도록 도와줍니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-social-share')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($quick_button_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($quick_button_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>빠른 버튼 생성</h3>
                        <p>다양한 용도로 활용 가능한 커스텀 버튼을 손 쉽게 생성할 수 있도록 도와줍니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-quick-button')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge active">활성화됨</span></p>
                        <h3>Ads 매니저</h3>
                        <p>Google AdSense 및 기타 광고 네트워크를 위한 ads.txt 파일을 관리합니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-ads')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($auto_index_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($auto_index_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>자동 인덱싱</h3>
                        <p>자동으로 포스트를 인덱싱하여 검색 엔진에 노출되도록 도와줍니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-auto-index')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
                <div class="pl-grid-item">
                    <header>
                        <p><span class="status-badge <?php echo ($header_footer_enabled === 'yes') ? esc_attr('active') : esc_attr('inactive'); ?>"><?php echo ($header_footer_enabled === 'yes') ? esc_html('활성화됨') : esc_html('비활성화됨'); ?></span></p>
                        <h3>헤더 & 푸터</h3>
                        <p>웹사이트의 헤더와 푸터에 HTML, CSS, JavaScript 코드를 삽입할 수 있습니다.</p>
                    </header>
                    <div class="pl-grid-item-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=presslearn-header-footer')); ?>" class="secondary-btn">설정</a>
                    </div>
                </div>
            </div>
        </div>
    </div>


