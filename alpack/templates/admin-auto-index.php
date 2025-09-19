<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

$current_tab = 'settings';
if (current_user_can('manage_options') && is_admin() && isset($_GET['tab'])) {
    $tab_value = sanitize_text_field(wp_unslash($_GET['tab']));
    if (in_array($tab_value, array('settings', 'logs', 'manual'))) {
        $current_tab = $tab_value;
    }
}

if (isset($_POST['presslearn_toggle_auto_index']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_auto_index_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_auto_index_enabled', $enable);
            $updated = true;
        }
    }
}

if (isset($_POST['save_auto_index_settings'])) {
    check_admin_referer('presslearn_save_auto_index_settings_nonce');
    
    if (current_user_can('manage_options')) {
        if (isset($_POST['indexnow_api_key'])) {
            $api_key = sanitize_text_field(wp_unslash($_POST['indexnow_api_key']));
            update_option('presslearn_indexnow_api_key', $api_key);
            
            if (!empty($api_key)) {
                $indexnow = presslearn_indexnow();
                if ($indexnow->validate_api_key($api_key)) {
                    $indexnow->create_api_key_file($api_key);
                }
            }
        }
        
        $auto_indexing_enabled = isset($_POST['auto_indexing_enabled']) ? 'yes' : 'no';
        update_option('presslearn_auto_indexing_enabled', $auto_indexing_enabled);
        
        $index_post_types = isset($_POST['index_post_types']) && is_array($_POST['index_post_types']) 
                           ? array_map('sanitize_text_field', array_map('wp_unslash', $_POST['index_post_types'])) 
                           : array();
        $valid_post_types = array();
        $allowed_post_types = array('post', 'page');
        
        foreach ($index_post_types as $post_type) {
            if (in_array($post_type, $allowed_post_types)) {
                $valid_post_types[] = $post_type;
            }
        }
        
        update_option('presslearn_index_post_types', $valid_post_types);
        
        $settings_updated = true;
    }
}

$auto_index_enabled = get_option('presslearn_auto_index_enabled', 'no');
$indexnow_api_key = get_option('presslearn_indexnow_api_key', '');
$auto_indexing_enabled = get_option('presslearn_auto_indexing_enabled', 'yes');
$index_post_types = get_option('presslearn_index_post_types', array('post', 'page'));
$has_api_key = !empty($indexnow_api_key);

?>

<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>자동 인덱싱</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($auto_index_enabled === 'yes'): ?>
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
            <span class="active">자동 인덱싱</span>
        </div>
    </div>

    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
    </div>
    <?php endif; ?>

    <?php if (isset($updated) && $updated): ?>
    <div class="notice notice-success inline">
        <p>자동 인덱싱 기능 설정이 변경되었습니다.</p>
    </div>
    <?php endif; ?>

    <?php if (!$has_api_key && $auto_index_enabled === 'yes'): ?>
    <div class="notice notice-warning inline">
        <p>IndexNow API 키를 설정해야 인덱싱 기능을 사용할 수 있습니다.<br/><a href="https://www.bing.com/indexnow" target="_blank">IndexNow API 키 발급 받기</a></p>
    </div>
    <?php endif; ?>

    <div class="presslearn-tabs">
        <a href="?page=presslearn-auto-index&tab=settings" class="tab-link <?php echo esc_attr($current_tab === 'settings' ? 'active' : ''); ?>">설정</a>
        <a href="?page=presslearn-auto-index&tab=logs" class="tab-link <?php echo esc_attr($current_tab === 'logs' ? 'active' : ''); ?>">로그</a>
        <a href="?page=presslearn-auto-index&tab=manual" class="tab-link <?php echo esc_attr($current_tab === 'manual' ? 'active' : ''); ?>">수동 인덱싱</a>
    </div>
    
    <?php if ($current_tab === 'settings'): ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>자동 인덱싱</h2>
            <p>IndexNow 프로토콜을 사용하여 네이버에 자동으로 인덱싱 요청을 보내는 기능입니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="auto-index-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_auto_index_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_auto_index" value="1">
                        <input type="hidden" name="enable" id="auto-index-enable-value" value="<?php echo esc_attr($auto_index_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($auto_index_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('auto-index-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('auto-index-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <form method="post" action="" id="auto-index-settings-form">
                <?php wp_nonce_field('presslearn_save_auto_index_settings_nonce'); ?>
                <input type="hidden" name="save_auto_index_settings" value="1">
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>IndexNow API 키</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <input type="text" name="indexnow_api_key" class="regular-text" value="<?php echo esc_attr($indexnow_api_key); ?>" placeholder="32자리 영숫자 API 키를 입력하세요">
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>자동 인덱싱 활성화</h3>
                        <p style="font-size: 13px; color: #666; margin-top: 5px;">게시글 발행/수정 시 자동으로 인덱싱 요청</p>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <label class="switch">
                            <input type="checkbox" name="auto_indexing_enabled" <?php echo esc_attr($auto_indexing_enabled === 'yes' ? 'checked' : ''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>인덱싱 대상 콘텐츠</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <div class="social-share-options">
                            <label class="social-share-button">
                                <input type="checkbox" name="index_post_types[]" value="post" <?php echo esc_attr(in_array('post', $index_post_types) ? 'checked' : ''); ?>>
                                <span class="social-btn">게시글</span>
                            </label><label class="social-share-button">
                                <input type="checkbox" name="index_post_types[]" value="page" <?php echo esc_attr(in_array('page', $index_post_types) ? 'checked' : ''); ?>>
                                <span class="social-btn">페이지</span>
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">IndexNow는 요청을 보낼 뿐, 인덱싱 결과를 보장하지 않습니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="auto-index-settings-form">저장하기</button>
            </div>
        </div>
    </div>
    
    <?php elseif ($current_tab === 'logs'): ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>인덱싱 로그</h2>
            <p>인덱싱 요청 결과와 상태를 확인할 수 있습니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <?php
                    global $wpdb;
                    $table_name = $wpdb->prefix . 'presslearn_indexing_logs';
                    $logs_per_page = 20;
                    $current_page = isset($_GET['logs_page']) ? max(1, intval($_GET['logs_page'])) : 1;
                    $logs_search = isset($_GET['logs_search']) ? sanitize_text_field($_GET['logs_search']) : '';
                    $offset = ($current_page - 1) * $logs_per_page;
                    
                    $logs = array();
                    $total_logs = 0;
                    
                    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
                        $where_clause = '';
                        $params = array();
                        
                        if (!empty($logs_search)) {
                            $where_clause = "WHERE post_title LIKE %s OR post_url LIKE %s";
                            $search_term = '%' . $wpdb->esc_like($logs_search) . '%';
                            $params[] = $search_term;
                            $params[] = $search_term;
                        }
                        
                        if (!empty($logs_search)) {
                            $total_logs = $wpdb->get_var($wpdb->prepare(
                                "SELECT COUNT(*) FROM $table_name $where_clause",
                                $params
                            ));
                        } else {
                            $total_logs = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                        }
                        
                        if (!empty($logs_search)) {
                            $logs = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM $table_name $where_clause ORDER BY indexed_at DESC LIMIT %d OFFSET %d",
                                array_merge($params, array($logs_per_page, $offset))
                            ), ARRAY_A);
                        } else {
                            $logs = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM $table_name ORDER BY indexed_at DESC LIMIT %d OFFSET %d",
                                $logs_per_page,
                                $offset
                            ), ARRAY_A);
                        }
                    }
                    
                    $max_pages = ceil($total_logs / $logs_per_page);
                    ?>
                    
                    <form method="get" action="" style="margin-bottom: 15px;">
                        <input type="hidden" name="page" value="presslearn-auto-index">
                        <input type="hidden" name="tab" value="logs">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" name="logs_search" class="regular-text" placeholder="게시글 제목 또는 URL 검색" style="width: 300px;" value="<?php echo esc_attr($logs_search); ?>">
                            <button type="submit" class="secondary-btn">검색</button>
                            <?php if (!empty($logs_search)): ?>
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'presslearn-auto-index', 'tab' => 'logs'))); ?>" class="button">전체 보기</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    
                    <?php if (empty($logs)): ?>
                        <?php if (!empty($logs_search)): ?>
                            <p style="text-align: center; color: #666; padding: 40px;">'<?php echo esc_html($logs_search); ?>' 검색 결과가 없습니다.</p>
                        <?php else: ?>
                            <p style="text-align: center; color: #666; padding: 40px;">아직 인덱싱 로그가 없습니다.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <table class="widefat">
                            <thead>
                                <tr>
                                    <th>게시글</th>
                                    <th>요청 유형</th>
                                    <th>응답 코드</th>
                                    <th>응답 메시지</th>
                                    <th>요청 시간</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($log['post_title']); ?></strong><br>
                                        <small><a href="<?php echo esc_url($log['post_url']); ?>" target="_blank"><?php echo esc_html($log['post_url']); ?></a></small>
                                    </td>
                                    <td>
                                        <?php if ($log['request_type'] === 'auto'): ?>
                                            <span style="background: #E3F2FD; color: #1976D2; padding: 2px 8px; border-radius: 12px; font-size: 11px;">자동</span>
                                        <?php elseif ($log['request_type'] === 'manual'): ?>
                                            <span style="background: #E8F5E9; color: #388E3C; padding: 2px 8px; border-radius: 12px; font-size: 11px;">수동</span>
                                        <?php else: ?>
                                            <span style="background: #FFF3E0; color: #F57C00; padding: 2px 8px; border-radius: 12px; font-size: 11px;">일괄</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $is_success = in_array($log['response_code'], array('200', '202'));
                                        $color = $is_success ? '#4CAF50' : '#f44336';
                                        ?>
                                        <span style="color: <?php echo esc_attr($color); ?>; font-weight: bold;"><?php echo esc_html($log['response_code'] ?: 'N/A'); ?></span>
                                    </td>
                                    <td><?php echo esc_html($log['response_message'] ?: ''); ?></td>
                                    <td><?php echo esc_html(mysql2date('Y-m-d H:i:s', $log['indexed_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($max_pages > 1): ?>
                        <div class="tablenav">
                            <div class="tablenav-pages">
                                <span class="displaying-num">총 <?php echo esc_html(number_format($total_logs)); ?>개 항목</span>
                                <span class="pagination-links">
                                    <?php 
                                    $logs_base_url = add_query_arg(array(
                                        'page' => 'presslearn-auto-index',
                                        'tab' => 'logs',
                                        'logs_search' => $logs_search
                                    ));
                                    ?>
                                    <?php if ($current_page > 1): ?>
                                        <a href="<?php echo esc_url(add_query_arg('logs_page', 1, $logs_base_url)); ?>" class="button">처음</a>
                                        <a href="<?php echo esc_url(add_query_arg('logs_page', $current_page - 1, $logs_base_url)); ?>" class="button">이전</a>
                                    <?php endif; ?>
                                    
                                    <span class="paging-input"><?php echo esc_html($current_page); ?> / <?php echo esc_html($max_pages); ?></span>
                                    
                                    <?php if ($current_page < $max_pages): ?>
                                        <a href="<?php echo esc_url(add_query_arg('logs_page', $current_page + 1, $logs_base_url)); ?>" class="button">다음</a>
                                        <a href="<?php echo esc_url(add_query_arg('logs_page', $max_pages, $logs_base_url)); ?>" class="button">마지막</a>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">응답 코드 200, 202는 성공을 의미하며, 실제 인덱싱은 검색 엔진의 판단에 따라 결정됩니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
            </div>
        </div>
    </div>
    
    <?php elseif ($current_tab === 'manual'): ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>수동 인덱싱</h2>
            <p>게시글을 수동으로 선택하여 인덱싱 요청을 보낼 수 있습니다. 인덱싱 상태는 가장 최근 요청 결과를 기준으로 표시됩니다.</p>
        </div>
        <div class="presslearn-card-body">
            <?php if (!$has_api_key): ?>
            <div class="notice notice-error inline">
                <p>수동 인덱싱을 사용하려면 먼저 IndexNow API 키를 설정해주세요.</p>
            </div>
            <?php else: ?>
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <?php
                    $posts_per_page = 20;
                    $current_page = isset($_GET['posts_page']) ? max(1, intval($_GET['posts_page'])) : 1;
                    $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                    
                    $args = array(
                        'post_type' => array('post', 'page'),
                        'post_status' => 'publish',
                        'posts_per_page' => $posts_per_page,
                        'paged' => $current_page,
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key' => '_presslearn_exclude_from_index',
                                'value' => 'yes',
                                'compare' => '!='
                            ),
                            array(
                                'key' => '_presslearn_exclude_from_index',
                                'compare' => 'NOT EXISTS'
                            )
                        )
                    );
                    
                    if (!empty($search_term)) {
                        $args['s'] = $search_term;
                    }
                    
                    $query = new WP_Query($args);
                    
                    function get_post_indexing_status($post_id) {
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'presslearn_indexing_logs';
                        
                        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                            return array(
                                'status' => 'never',
                                'label' => '미요청',
                                'class' => 'never',
                                'last_indexed' => null
                            );
                        }
                        
                        $recent_log = $wpdb->get_row($wpdb->prepare(
                            "SELECT response_code, indexed_at, request_type 
                             FROM $table_name 
                             WHERE post_id = %d 
                             ORDER BY indexed_at DESC 
                             LIMIT 1",
                            $post_id
                        ));
                        
                        if (!$recent_log) {
                            return array(
                                'status' => 'never',
                                'label' => '미요청',
                                'class' => 'never',
                                'last_indexed' => null
                            );
                        }
                        
                        $success_codes = array('200', '202');
                        $is_success = in_array($recent_log->response_code, $success_codes);
                        
                        if ($is_success) {
                            return array(
                                'status' => 'success',
                                'label' => '성공',
                                'class' => 'success',
                                'last_indexed' => $recent_log->indexed_at
                            );
                        } else {
                            return array(
                                'status' => 'failed',
                                'label' => '실패',
                                'class' => 'failed',
                                'last_indexed' => $recent_log->indexed_at
                            );
                        }
                    }
                    ?>
                    
                    <form method="get" action="" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <input type="hidden" name="page" value="presslearn-auto-index">
                        <input type="hidden" name="tab" value="manual">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" name="search" class="regular-text" placeholder="게시글 제목 검색" style="width: 250px;" value="<?php echo esc_attr($search_term); ?>">
                            <button type="submit" class="secondary-btn">검색</button>
                        </div>
                        <div>
                            <button type="button" class="point-btn" id="bulk-index-btn" disabled>선택된 게시글 인덱싱</button>
                        </div>
                    </form>
                    
                    <?php if (!$query->have_posts()): ?>
                        <p style="text-align: center; color: #666; padding: 40px;">인덱싱할 게시글이 없습니다.</p>
                    <?php else: ?>
                        <form id="bulk-index-form">
                            <?php wp_nonce_field('presslearn_indexing_nonce', 'bulk_index_nonce'); ?>
                            <table class="widefat">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><input type="checkbox" id="select-all-posts"></th>
                                        <th>제목</th>
                                        <th>유형</th>
                                        <th>작성일</th>
                                        <th>인덱싱 상태</th>
                                        <th>동작</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($query->have_posts()): $query->the_post(); ?>
                                    <?php 
                                    $post_id = get_the_ID();
                                    $indexing_status = get_post_indexing_status($post_id);
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" class="post-checkbox" name="post_ids[]" value="<?php echo esc_attr($post_id); ?>"></td>
                                        <td>
                                            <strong><?php echo esc_html(get_the_title()); ?></strong><br>
                                            <small><a href="<?php echo esc_url(get_permalink()); ?>" target="_blank"><?php echo esc_html(get_permalink()); ?></a></small>
                                        </td>
                                        <td>
                                            <?php if (get_post_type() === 'post'): ?>
                                                <span style="background: #E3F2FD; color: #1976D2; padding: 2px 8px; border-radius: 12px; font-size: 11px;">게시글</span>
                                            <?php else: ?>
                                                <span style="background: #E8F5E9; color: #388E3C; padding: 2px 8px; border-radius: 12px; font-size: 11px;">페이지</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo esc_html(get_the_date('Y-m-d H:i:s')); ?></td>
                                        <td>
                                            <?php 
                                            if ($indexing_status['class'] === 'success') {
                                                $style = 'background: #E8F5E9; color: #2E7D32; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;';
                                            } elseif ($indexing_status['class'] === 'failed') {
                                                $style = 'background: #FFEBEE; color: #C62828; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;';
                                            } else {
                                                $style = 'background: #F5F5F5; color: #666; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 500;';
                                            }
                                            $tooltip = $indexing_status['last_indexed'] 
                                                       ? '마지막 요청: ' . mysql2date('Y-m-d H:i:s', $indexing_status['last_indexed'])
                                                       : '아직 인덱싱 요청을 하지 않았습니다';
                                            ?>
                                            <span style="<?php echo esc_attr($style); ?>" title="<?php echo esc_attr($tooltip); ?>"><?php echo esc_html($indexing_status['label']); ?></span>
                                        </td>
                                        <td>
                                            <button type="button" class="point-btn index-single-btn" data-post-id="<?php echo esc_attr($post_id); ?>">인덱싱</button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </form>
                        
                        <?php if ($query->max_num_pages > 1): ?>
                        <div class="tablenav">
                            <div class="tablenav-pages">
                                <span class="displaying-num">총 <?php echo esc_html(number_format($query->found_posts)); ?>개 항목</span>
                                <span class="pagination-links">
                                    <?php 
                                    $base_url = add_query_arg(array(
                                        'page' => 'presslearn-auto-index',
                                        'tab' => 'manual',
                                        'search' => $search_term
                                    ));
                                    ?>
                                    <?php if ($current_page > 1): ?>
                                        <a href="<?php echo esc_url(add_query_arg('posts_page', 1, $base_url)); ?>" class="button">처음</a>
                                        <a href="<?php echo esc_url(add_query_arg('posts_page', $current_page - 1, $base_url)); ?>" class="button">이전</a>
                                    <?php endif; ?>
                                    
                                    <span class="paging-input"><?php echo esc_html($current_page); ?> / <?php echo esc_html($query->max_num_pages); ?></span>
                                    
                                    <?php if ($current_page < $query->max_num_pages): ?>
                                        <a href="<?php echo esc_url(add_query_arg('posts_page', $current_page + 1, $base_url)); ?>" class="button">다음</a>
                                        <a href="<?php echo esc_url(add_query_arg('posts_page', $query->max_num_pages, $base_url)); ?>" class="button">마지막</a>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php wp_reset_postdata(); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">한 번에 최대 50개까지 선택하여 일괄 인덱싱할 수 있습니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-posts');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateBulkIndexButton();
        });
    }
    
    const postCheckboxes = document.querySelectorAll('.post-checkbox');
    postCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', updateBulkIndexButton);
    });
    
    const indexButtons = document.querySelectorAll('.index-single-btn');
    indexButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            indexSinglePost(postId, this);
        });
    });
    
    function updateBulkIndexButton() {
        const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
        const bulkButton = document.getElementById('bulk-index-btn');
        if (bulkButton) {
            bulkButton.disabled = checkedBoxes.length === 0;
            bulkButton.textContent = checkedBoxes.length > 0 ? '선택된 ' + checkedBoxes.length + '개 게시글 인덱싱' : '선택된 게시글 인덱싱';
        }
    }
    
    function indexSinglePost(postId, button) {
        const originalText = button.textContent;
        button.textContent = '처리중';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('action', 'presslearn_manual_index');
        formData.append('nonce', '<?php echo esc_js(wp_create_nonce('presslearn_indexing_nonce')); ?>');
        formData.append('post_id', postId);
        
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('인덱싱 요청이 성공적으로 전송되었습니다.');
                location.reload();
            } else {
                alert('인덱싱 요청 실패: ' + (data.data.message || '알 수 없는 오류'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('인덱싱 요청 중 오류가 발생했습니다.');
        })
        .finally(function() {
            button.textContent = originalText;
            button.disabled = false;
        });
    }
    
    const bulkIndexButton = document.getElementById('bulk-index-btn');
    if (bulkIndexButton) {
        bulkIndexButton.addEventListener('click', function() {
            const checkedBoxes = document.querySelectorAll('.post-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('인덱싱할 게시글을 선택해주세요.');
                return;
            }
            
            if (checkedBoxes.length > 50) {
                alert('한 번에 최대 50개까지만 선택할 수 있습니다.');
                return;
            }
            
            if (!confirm(checkedBoxes.length + '개의 게시글을 인덱싱하시겠습니까?')) {
                return;
            }
            
            const postIds = Array.from(checkedBoxes).map(function(checkbox) {
                return checkbox.value;
            });
            
            const originalText = this.textContent;
            this.textContent = '처리중';
            this.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'presslearn_bulk_index');
            formData.append('nonce', '<?php echo esc_js(wp_create_nonce('presslearn_indexing_nonce')); ?>');
            postIds.forEach(function(postId) {
                formData.append('post_ids[]', postId);
            });
            
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.data.message);
                    location.reload();
                } else {
                    alert('일괄 인덱싱 실패: ' + (data.data.message || '알 수 없는 오류'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('일괄 인덱싱 중 오류가 발생했습니다.');
            })
            .finally(() => {
                this.textContent = originalText;
                this.disabled = false;
            });
        });
    }
    
    updateBulkIndexButton();
});
</script>

