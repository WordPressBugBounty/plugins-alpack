<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (isset($_POST['presslearn_toggle_analytics']) && isset($_POST['enable'])) {
    check_admin_referer('presslearn_toggle_analytics_nonce');
    
    if (current_user_can('manage_options')) {
        $enable = sanitize_text_field(wp_unslash($_POST['enable']));
        if ($enable === 'yes' || $enable === 'no') {
            update_option('presslearn_analytics_enabled', $enable);
            $updated = true;
        }
    }
}

$current_tab = 'settings';
$chart_period = 'daily';
$start_date = '';
$end_date = '';
$pages_page = 1;
$countries_page = 1;
$referrers_page = 1;
$keywords_page = 1;

if (current_user_can('manage_options') && is_admin()) {
    if (isset($_GET['tab'])) {
        $tab_value = sanitize_text_field(wp_unslash($_GET['tab']));
        if (in_array($tab_value, array('settings', 'statistics'))) {
            $current_tab = $tab_value;
        }
    }
    
    if (isset($_GET['chart_period'])) {
        $period_value = sanitize_text_field(wp_unslash($_GET['chart_period']));
        if (in_array($period_value, array('daily', 'weekly', 'yearly'))) {
            $chart_period = $period_value;
        }
    }
    
    if (isset($_GET['start_date'])) {
        $start_date_value = sanitize_text_field(wp_unslash($_GET['start_date']));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date_value)) {
            $start_date = $start_date_value;
        }
    }
    
    if (isset($_GET['end_date'])) {
        $end_date_value = sanitize_text_field(wp_unslash($_GET['end_date']));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date_value)) {
            $end_date = $end_date_value;
        }
    }
    
    if (isset($_GET['pages_page'])) {
        $pages_page = max(1, intval(wp_unslash($_GET['pages_page'])));
    }
    
    if (isset($_GET['countries_page'])) {
        $countries_page = max(1, intval(wp_unslash($_GET['countries_page'])));
    }
    
    if (isset($_GET['referrers_page'])) {
        $referrers_page = max(1, intval(wp_unslash($_GET['referrers_page'])));
    }
    
    if (isset($_GET['keywords_page'])) {
        $keywords_page = max(1, intval(wp_unslash($_GET['keywords_page'])));
    }
}

if (isset($_POST['save_analytics_settings'])) {
    check_admin_referer('presslearn_save_analytics_settings_nonce');
    
    if (current_user_can('manage_options')) {
        $analytics_exclude_admin = isset($_POST['analytics_exclude_admin']) ? sanitize_text_field(wp_unslash($_POST['analytics_exclude_admin'])) : '';
        update_option('presslearn_analytics_exclude_admin', $analytics_exclude_admin);
        
        $use_cloudflare = isset($_POST['use_cloudflare']) ? 'yes' : 'no';
        update_option('presslearn_analytics_use_cloudflare', $use_cloudflare);
        
        $cache_time = isset($_POST['analytics_cache_time']) ? intval(wp_unslash($_POST['analytics_cache_time'])) : 300;
        if ($cache_time < 60) $cache_time = 60;
        if ($cache_time > 86400) $cache_time = 86400;
        update_option('presslearn_analytics_cache_time', $cache_time);
        
        $settings_updated = true;
    }
}

$analytics_enabled = get_option('presslearn_analytics_enabled', 'no');
$analytics_exclude_admin = get_option('presslearn_analytics_exclude_admin', '');
$use_cloudflare = get_option('presslearn_analytics_use_cloudflare', 'no');
$analytics_cache_time = get_option('presslearn_analytics_cache_time', 300);


if (function_exists('presslearn_plugin') && method_exists(presslearn_plugin(), 'create_analytics_tables')) {
    presslearn_plugin()->create_analytics_tables();
}

$pages_current_page = 1;
$countries_current_page = 1;
$referrers_current_page = 1;
$keywords_current_page = 1;
$items_per_page = 10;
$keywords_per_page = 20;

if (current_user_can('manage_options') && is_admin()) {
    $pages_current_page = $pages_page;
    $countries_current_page = $countries_page;
    $referrers_current_page = $referrers_page;
    $keywords_current_page = $keywords_page;
    
    $allowed_tabs = array('settings', 'statistics', 'pages', 'countries', 'referrers', 'keywords');
    if (!in_array($current_tab, $allowed_tabs)) {
        $current_tab = 'settings';
    }
    
    $allowed_periods = array('daily', 'weekly', 'yearly');
    if (!in_array($chart_period, $allowed_periods)) {
        $chart_period = 'daily';
    }
    
    $pages_current_page = max(1, $pages_current_page);
    $countries_current_page = max(1, $countries_current_page);
    $referrers_current_page = max(1, $referrers_current_page);
    $keywords_current_page = max(1, $keywords_current_page);
}

$default_end_date = gmdate('Y-m-d');
$default_start_date = gmdate('Y-m-d');

if (empty($start_date)) {
    $start_date = $default_start_date;
}

if ($current_tab === 'statistics' && empty($start_date) && empty($end_date)) {
    $start_date = $default_start_date;
    $end_date = $default_end_date;
} else {
    if (current_user_can('manage_options') && is_admin()) {
        if (empty($start_date)) {
            $start_date = $default_start_date;
        }
        if (empty($end_date)) {
            $end_date = $default_end_date;
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) {
            $start_date = $default_start_date;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
            $end_date = $default_end_date;
        }
    } else {
        $start_date = $default_start_date;
        $end_date = $default_end_date;
    }
}

$start_date_obj = new DateTime($start_date);
$end_date_obj = new DateTime($end_date);
$date_diff = $start_date_obj->diff($end_date_obj);
$period_days = $date_diff->days + 1;

$period_days = max(1, $period_days);

if ($chart_period === 'daily') {
    $period_days = min($period_days, 60);
} elseif ($chart_period === 'weekly') {
    $period_days = min($period_days, 365);
} elseif ($chart_period === 'yearly') {
    $period_days = min($period_days, 1825);
}

function alpack_get_analytics_stats($period = 'today', $days = 30, $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_analytics_stats_' . md5($period . '_' . $days . '_' . $start_date . '_' . $end_date);
    $cached_stats = get_transient($cache_key);
    
    if ($cached_stats !== false) {
        return $cached_stats;
    }
    
    $stats = [];
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
        $where_clause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $where_clause = "WHERE created_at >= %s AND created_at <= %s";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        } else {
            if($period == 'today') {
                $where_clause = "WHERE DATE(created_at) = CURDATE()";
            } elseif($period == 'week') {
                $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
                $params[] = intval(min($days, 7));
            } else {
                $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
                $params[] = intval($days);
            }
        }
        
        $pageviews = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_pageviews $where_clause", $params));
        $visitors = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT visitor_id) FROM $table_pageviews $where_clause", $params));
        $ip_visitors = $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT ip) FROM $table_pageviews $where_clause", $params));
        
        $stats = [
            'pageviews' => intval($pageviews),
            'visitors' => intval($visitors),
            'ip_visitors' => intval($ip_visitors)
        ];
    } else {
        $stats = [
            'pageviews' => 0,
            'visitors' => 0,
            'ip_visitors' => 0
        ];
    }
    
    set_transient($cache_key, $stats, $cache_time);
    
    return $stats;
}

function alpack_get_popular_pages($limit = 12, $days = 30, $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_popular_pages_' . md5($limit . '_' . $days . '_' . $start_date . '_' . $end_date);
    $cached_pages = get_transient($cache_key);
    
    if ($cached_pages !== false) {
        return $cached_pages;
    }
    
    $popular_pages = [];
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
        $where_clause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $where_clause = "WHERE created_at >= %s AND created_at <= %s";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        } else {
            $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
            $params[] = $days;
        }
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT url, title, COUNT(*) as views
            FROM $table_pageviews
            $where_clause
            GROUP BY url, title
            ORDER BY views DESC
                LIMIT %d",
                array_merge($params, [$limit])
            ),
            ARRAY_A
        );
        
        if($results) {
            foreach($results as $row) {
                $popular_pages[] = [
                    'url' => $row['url'],
                    'title' => $row['title'],
                    'views' => intval($row['views'])
                ];
            }
        }
    }
    
    if(empty($popular_pages)) {
        $popular_pages = [
            [
                'url' => home_url('/'),
                'title' => '홈',
                'views' => 0
            ]
        ];
    }
    
    set_transient($cache_key, $popular_pages, $cache_time);
    
    return $popular_pages;
}

function alpack_get_visitor_countries($limit = 15, $days = 30, $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_visitor_countries_' . md5($limit . '_' . $days . '_' . $start_date . '_' . $end_date);
    $cached_countries = get_transient($cache_key);
    
    if ($cached_countries !== false) {
        return $cached_countries;
    }
    
    $visitor_countries = [];
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
        $where_clause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $where_clause = "WHERE created_at >= %s AND created_at <= %s";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        } else {
            $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
            $params[] = $days;
        }
        
        $where_clause .= " AND country != ''";
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT country, COUNT(DISTINCT visitor_id) as count
            FROM $table_pageviews
            $where_clause
            GROUP BY country
            ORDER BY count DESC
                LIMIT %d",
                array_merge($params, [$limit])
            ),
            ARRAY_A
        );
        
        if($results) {
            foreach($results as $row) {
                $visitor_countries[] = [
                    'country' => $row['country'],
                    'count' => intval($row['count'])
                ];
            }
        }
    }
    
    if(empty($visitor_countries)) {
        $visitor_countries = [
            ['country' => '데이터 없음', 'count' => 0]
        ];
    }
    
    set_transient($cache_key, $visitor_countries, $cache_time);
    
    return $visitor_countries;
}

function alpack_get_organic_keywords($limit = 20, $days = 30, $offset = 0, $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_organic_keywords_' . md5($limit . '_' . $days . '_' . $offset . '_' . $start_date . '_' . $end_date);
    $cached_keywords = get_transient($cache_key);
    
    if ($cached_keywords !== false) {
        return $cached_keywords;
    }
    
    $organic_keywords = [];
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
        $where_clause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $where_clause = "WHERE created_at >= %s AND created_at <= %s";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        } else {
            $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
            $params[] = intval($days);
        }
        
        $where_clause .= " AND referrer != ''";
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                referrer,
                COUNT(*) as count
            FROM $table_pageviews
            $where_clause
            GROUP BY referrer
                ORDER BY count DESC",
                $params
            ),
            ARRAY_A
        );
        
        if($results) {
            $keywords_found = [];
            
            foreach($results as $row) {
                $referrer = $row['referrer'];
                $keyword = '';
                $search_engine = '';
                $search_engine_display = '';
                
                if(strpos($referrer, 'google') !== false && strpos($referrer, 'search') !== false) {
                    $search_engine = '구글';
                    $search_engine_display = '구글';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '';
                    
                    if(empty($keyword)) {
                        $keyword = '(not provided)';
                    }
                } 
                elseif(strpos($referrer, 'google.co.kr') !== false || strpos($referrer, 'google.com') !== false) {
                    $search_engine = '구글';
                    $search_engine_display = '구글';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '';
                    
                    if(empty($keyword)) {
                        $keyword = '(not provided)';
                    }
                } 
                elseif(strpos($referrer, 'search.naver.com') !== false || strpos($referrer, 'naver.com') !== false) {
                    $search_engine = '네이버';
                    if(strpos($referrer, 'm.search.naver.com') !== false || strpos($referrer, 'm.naver.com') !== false) {
                        $search_engine_display = '네이버 모바일';
                    } else {
                        $search_engine_display = '네이버';
                    }
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['query']) ? $params['query'] : '';
                }
                elseif((strpos($referrer, 'daum.net') !== false && strpos($referrer, 'search') !== false) || 
                       strpos($referrer, 'search.daum.net') !== false) {
                    $search_engine = '다음';
                    $search_engine_display = '다음';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '';
                    if(empty($keyword)) {
                        $keyword = isset($params['w']) ? $params['w'] : '';
                    }
                }
                elseif(strpos($referrer, 'bing.com') !== false && strpos($referrer, 'search') !== false) {
                    $search_engine = '빙';
                    $search_engine_display = '빙';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '';
                }
                elseif(strpos($referrer, 'yahoo.com') !== false && strpos($referrer, 'search') !== false) {
                    $search_engine = '야후';
                    $search_engine_display = '야후';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['p']) ? $params['p'] : '';
                }
                
                if(!empty($search_engine)) {
                    if(empty($keyword) && $search_engine === 'Google') {
                        $keyword = '(not provided)';
                    }
                    
                    if(!empty($keyword)) {
                        $keyword_key = md5($keyword . $search_engine);
                        
                        if(isset($keywords_found[$keyword_key])) {
                            $keywords_found[$keyword_key]['count'] += intval($row['count']);
                        } else {
                            $keywords_found[$keyword_key] = [
                                'keyword' => $keyword,
                                'search_engine' => $search_engine,
                                'search_engine_display' => $search_engine_display,
                                'count' => intval($row['count']),
                                'referrer' => $referrer
                            ];
                        }
                    }
                }
            }
            
            usort($keywords_found, function($a, $b) {
                return $b['count'] - $a['count'];
            });
            
            $organic_keywords = array_slice(array_values($keywords_found), $offset, $limit);
        }
    }
    
    if(empty($organic_keywords)) {
        $organic_keywords = [
            [
                'keyword' => '아직 수집된 키워드가 없습니다', 
                'count' => 0, 
                'search_engine' => 'None',
                'search_engine_display' => 'None'
            ]
        ];
    }
    
    set_transient($cache_key, $organic_keywords, $cache_time);
    
    return $organic_keywords;
}

function alpack_get_total_organic_keywords_count($days = 30, $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_total_keywords_count_' . md5($days . '_' . $start_date . '_' . $end_date);
    $cached_count = get_transient($cache_key);
    
    if ($cached_count !== false) {
        return $cached_count;
    }
    
    $count = 0;
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
        $where_clause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $where_clause = "WHERE created_at >= %s AND created_at <= %s";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        } else {
            $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)";
            $params[] = $days;
        }
        
        $where_clause .= " AND referrer != ''";
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT referrer
            FROM $table_pageviews
            $where_clause
                GROUP BY referrer",
                $params
            ),
            ARRAY_A
        );
        
        if($results) {
            $unique_keywords = [];
            
            foreach($results as $row) {
                $referrer = $row['referrer'];
                $keyword = '';
                $search_engine = '';
                
                if(strpos($referrer, 'google') !== false && strpos($referrer, 'search') !== false) {
                    $search_engine = '구글';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '(not provided)';
                } 
                elseif(strpos($referrer, 'google.co.kr') !== false || strpos($referrer, 'google.com') !== false) {
                    $search_engine = '구글';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '(not provided)';
                } 
                elseif(strpos($referrer, 'search.naver.com') !== false || strpos($referrer, 'naver.com') !== false) {
                    $search_engine = '네이버';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['query']) ? $params['query'] : '';
                }
                elseif((strpos($referrer, 'daum.net') !== false && strpos($referrer, 'search') !== false) || 
                       strpos($referrer, 'search.daum.net') !== false) {
                    $search_engine = '다음';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '';
                    if(empty($keyword)) {
                        $keyword = isset($params['w']) ? $params['w'] : '';
                    }
                }
                elseif(strpos($referrer, 'bing.com') !== false && strpos($referrer, 'search') !== false) {
                    $search_engine = '빙';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['q']) ? $params['q'] : '';
                }
                elseif(strpos($referrer, 'yahoo.com') !== false && strpos($referrer, 'search') !== false) {
                    $search_engine = '야후';
                    parse_str(wp_parse_url($referrer, PHP_URL_QUERY), $params);
                    $keyword = isset($params['p']) ? $params['p'] : '';
                }
                
                if(!empty($search_engine)) {
                    if(empty($keyword) && $search_engine === 'Google') {
                        $keyword = '(not provided)';
                    }
                    
                    if(!empty($keyword)) {
                        $key = md5($keyword . $search_engine);
                        if(!isset($unique_keywords[$key])) {
                            $unique_keywords[$key] = true;
                            $count++;
                        }
                    }
                }
            }
        }
    }
    
    set_transient($cache_key, $count, $cache_time);
    
    return $count;
}

function alpack_get_referrers($limit = 15, $days = 30, $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    $table_referrers = $wpdb->prefix . 'presslearn_referrers';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_referrers_' . md5($limit . '_' . $days . '_' . $start_date . '_' . $end_date);
    $cached_referrers = get_transient($cache_key);
    
    if ($cached_referrers !== false) {
        return $cached_referrers;
    }
    
    $referrers = [];
    
    $site_host = wp_parse_url(site_url(), PHP_URL_HOST);
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
        $where_clause = '';
        $params = [];
        
        if ($start_date && $end_date) {
            $where_clause = "WHERE created_at >= %s AND created_at <= %s AND referrer != ''";
            $params[] = $start_date . ' 00:00:00';
            $params[] = $end_date . ' 23:59:59';
        } else {
            $where_clause = "WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY) AND referrer != ''";
            $params[] = intval($days);
        }
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT 
                    referrer,
                    COUNT(*) as count
                FROM $table_pageviews
                $where_clause
                GROUP BY referrer
                ORDER BY count DESC",
                $params
            ),
            ARRAY_A
        );
        
        if(!empty($results)) {
            $referrer_groups = [];
            
            foreach($results as $row) {
                $referrer = $row['referrer'];
                $count = intval($row['count']);
                
                if (empty($referrer)) continue;
                
                $referrer_host = wp_parse_url($referrer, PHP_URL_HOST);
                if (empty($referrer_host)) continue;
                
                $group_name = $referrer_host;
                $type = '외부 유입';
                
                if (strpos($referrer_host, 'google') !== false && (strpos($referrer_host, 'search') !== false || $referrer_host === 'google.com' || $referrer_host === 'google.co.kr' || preg_match('/^(www\.)?google\..+$/', $referrer_host))) {
                    $group_name = '구글';
                    $type = '자연 유입';
                } elseif (strpos($referrer_host, 'naver.com') !== false) {
                    if (strpos($referrer_host, 'blog.naver.com') !== false || strpos($referrer_host, 'm.blog.naver.com') !== false) {
                        $group_name = '네이버 블로그';
                        $type = '외부 유입';
                    } else {
                        $group_name = '네이버';
                        $type = '자연 유입';
                    }
                } elseif (strpos($referrer_host, 'daum.net') !== false) {
                    $group_name = '다음';
                    $type = '자연 유입';
                } elseif (strpos($referrer_host, 'facebook.com') !== false) {
                    $group_name = '페이스북';
                    $type = '외부 유입';
                } elseif (strpos($referrer_host, 'instagram.com') !== false) {
                    $group_name = '인스타그램';
                    $type = '외부 유입';
                } elseif (strpos($referrer_host, 'twitter.com') !== false) {
                    $group_name = '트위터';
                    $type = '외부 유입';
                } elseif (strpos($referrer_host, 'bing.com') !== false) {
                    $group_name = '빙';
                    $type = '자연 유입';
                } elseif (strpos($referrer_host, 'yahoo.com') !== false) {
                    $group_name = '야후';
                    $type = '자연 유입';
                } elseif (strpos($referrer_host, 'youtube.com') !== false) {
                    $group_name = '유튜브';
                    $type = '외부 유입';
                } elseif (strpos($referrer_host, 'kakao') !== false) {
                    $group_name = '카카오톡';
                    $type = '외부 유입';
                } else {
                    $type = '외부 유입';
                }
                
                if (!isset($referrer_groups[$group_name])) {
                    $referrer_groups[$group_name] = [
                        'referrer' => $group_name,
                        'count' => 0,
                        'details' => [],
                        'total_urls' => 0,
                        'type' => $type
                    ];
                }
                
                $referrer_groups[$group_name]['count'] += $count;
                $referrer_groups[$group_name]['details'][] = [
                    'url' => $referrer,
                    'count' => $count
                ];
                $referrer_groups[$group_name]['total_urls']++;
            }
            
            foreach ($referrer_groups as $key => $group) {
                usort($referrer_groups[$key]['details'], function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                if (count($referrer_groups[$key]['details']) > 10) {
                    $referrer_groups[$key]['details'] = array_slice($referrer_groups[$key]['details'], 0, 10);
                }
            }
            
            $referrers = array_values($referrer_groups);
            usort($referrers, function($a, $b) {
                return $b['count'] - $a['count'];
            });
        } else {
        }
    }
    
    $has_direct = false;
    foreach($referrers as $ref) {
        if($ref['referrer'] === '직접 접속') {
            $has_direct = true;
            break;
        }
    }
    
    if(!$has_direct) {
        $direct_count = 0;
        
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
            if ($start_date && $end_date) {
                $direct_count = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM $table_pageviews 
                    WHERE (referrer = '' OR referrer IS NULL)
                    AND created_at >= %s AND created_at <= %s
                ", $start_date . ' 00:00:00', $end_date . ' 23:59:59'));
            } else {
                $direct_count = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM $table_pageviews 
                    WHERE (referrer = '' OR referrer IS NULL)
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
                ", $days));
            }
        }
        
        if($direct_count > 0) {
            array_unshift($referrers, [
                'referrer' => '직접 접속',
                'count' => intval($direct_count),
                'details' => [
                    ['url' => '네이버 QR 링크 혹은 직접 URL 입력', 'count' => intval($direct_count)]
                ],
                'total_urls' => 1,
                'type' => '직접 유입'
            ]);
        }
    }
    
    if(empty($referrers)) {
        $pageviews_count = 0;
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) == $table_pageviews) {
            if ($start_date && $end_date) {
                $pageviews_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_pageviews WHERE created_at >= %s AND created_at <= %s",
                    $start_date . ' 00:00:00', $end_date . ' 23:59:59'
                ));
            } else {
                $pageviews_count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_pageviews WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL %d DAY)",
                    $days
                ));
            }
        }
        
        if($pageviews_count > 0) {
            $referrers = [
                [
                    'referrer' => '직접 접속', 
                    'count' => intval($pageviews_count),
                    'details' => [
                        ['url' => '네이버 QR 링크 혹은 직접 URL 입력', 'count' => intval($pageviews_count)]
                    ],
                    'total_urls' => 1,
                    'type' => '직접 유입'
                ]
            ];
        } else {
            $referrers = [
                [
                    'referrer' => '데이터 없음',
                    'count' => 0,
                    'details' => [
                        ['url' => '아직 레퍼러 데이터가 수집되지 않았습니다', 'count' => 0]
                    ],
                    'total_urls' => 0,
                    'type' => ''
                ]
            ];
        }
    }
    
    $result = array_slice($referrers, 0, $limit);
    
    set_transient($cache_key, $result, $cache_time);
    
    return $result;
}

function alpack_get_chart_data($period = 'daily', $start_date = null, $end_date = null) {
    global $wpdb;
    $table_pageviews = $wpdb->prefix . 'presslearn_pageviews';
    
    $cache_time = get_option('presslearn_analytics_cache_time', 300);
    $cache_key = 'alpack_chart_data_' . md5($period . '_' . $start_date . '_' . $end_date);
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    $data = [
        'labels' => [],
        'pageviews' => [],
        'visitors' => [],
        'ip_visitors' => [],
        'raw_dates' => []
    ];
    
    if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_pageviews)) != $table_pageviews) {
        set_transient($cache_key, $data, $cache_time);
        return $data;
    }
    
    $limit = 60;
    $today = gmdate('Y-m-d');
    
    if ($start_date && $end_date && $start_date == $end_date && $start_date == $today) {
        $select_period = "HOUR(created_at)";
        $select_label = "HOUR(created_at)";
        $limit = 24;
    } 
    else if ($period === 'weekly') {
        $select_period = "YEARWEEK(created_at, 1)";
        $select_label = "MIN(created_at)";
        $limit = 52;
    } 
    else if ($period === 'yearly') {
        $select_period = "YEAR(created_at)";
        $select_label = "YEAR(created_at)";
        $group_by = "YEAR(created_at)";
        $limit = 10;
    }
    else {
        $select_period = "DATE(created_at)";
        $select_label = "DATE(created_at)";
    }
    
    $table_pageviews = esc_sql($table_pageviews);
    $select_period = esc_sql($select_period);
    $select_label = esc_sql($select_label);
    
    $where_clause = '';
    $sql_params = [];
    
    if ($start_date && $end_date) {
        $where_clause = "WHERE created_at >= %s AND created_at <= %s";
        $sql_params[] = $start_date . ' 00:00:00';
        $sql_params[] = $end_date . ' 23:59:59';
    }
    
    $sql = "SELECT 
        {$select_period} as period,
        {$select_label} as period_label,
        MIN(created_at) as period_start,
        MAX(created_at) as period_end,
        COUNT(*) as pageviews,
        COUNT(DISTINCT visitor_id) as visitors,
        COUNT(DISTINCT ip) as ip_visitors
    FROM {$table_pageviews}";
    
    if (!empty($where_clause)) {
        $sql .= " $where_clause";
    }
    
    $sql .= " GROUP BY " . (isset($group_by) ? $group_by : "period") . " ORDER BY period ASC LIMIT %d";
    $sql_params[] = $limit;
    
    $results = $wpdb->get_results(
        $wpdb->prepare($sql, $sql_params),
        ARRAY_A
    );
    
    if ($results) {
        if ($start_date && $end_date && $start_date == $end_date && $start_date == $today) {
            $hour_data = [];
            foreach ($results as $row) {
                $hour = intval($row['period_label']);
                $hour_data[$hour] = $row;
            }
            
            $results = [];
            for ($hour = 0; $hour < 24; $hour++) {
                if (isset($hour_data[$hour])) {
                    $results[] = $hour_data[$hour];
                } else {
                    $hour_str = sprintf('%02d', $hour);
                    $results[] = [
                        'period' => $hour,
                        'period_label' => $hour_str,
                        'period_start' => gmdate('Y-m-d H:i:s', strtotime("$today $hour_str:00:00")),
                        'period_end' => gmdate('Y-m-d H:i:s', strtotime("$today $hour_str:59:59")),
                        'pageviews' => 0,
                        'visitors' => 0,
                        'ip_visitors' => 0
                    ];
                }
            }
        }
        
        foreach ($results as $row) {
            if ($start_date && $end_date && $start_date == $end_date && $start_date == $today) {
                $hour = intval($row['period_label']);
                $data['labels'][] = $hour . '시';
            } 
            else if ($period === 'weekly') {
                $week_start = new DateTime($row['period_start']);
                $week_end = new DateTime($row['period_end']);
                $data['labels'][] = $week_start->format('m/d') . '~' . $week_end->format('m/d');
            } 
            else if ($period === 'yearly') {
                if (!empty($row['period_label'])) {
                    $year = intval($row['period_label']);
                    $data['labels'][] = $year . '년';
                } else if (!empty($row['period_start'])) {
                    $year_date = new DateTime($row['period_start']);
                    $data['labels'][] = $year_date->format('Y년');
            } else {
                    $data['labels'][] = '데이터 없음';
                }
            } 
            else {
                $day_date = new DateTime($row['period_label']);
                $data['labels'][] = $day_date->format('m/d');
            }
            
            $data['pageviews'][] = intval($row['pageviews']);
            $data['visitors'][] = intval($row['visitors']);
            $data['ip_visitors'][] = intval($row['ip_visitors']);
            
            $data['raw_dates'][] = [
                'start' => $row['period_start'],
                'end' => $row['period_end'],
                'period' => ($start_date && $end_date && $start_date == $end_date && $start_date == $today) ? 'hourly' : $period
            ];
        }
    } else {
        set_transient($cache_key, $data, $cache_time);
        return $data;
    }
    
    set_transient($cache_key, $data, $cache_time);
    
    return $data;
}

$dummy_stats = [
    'today' => alpack_get_analytics_stats('today', 0, $start_date, $end_date),
    'week' => alpack_get_analytics_stats('week', $period_days, $start_date, $end_date),
    'month' => alpack_get_analytics_stats('month', $period_days, $start_date, $end_date)
];

$popular_pages = alpack_get_popular_pages(50, $period_days, $start_date, $end_date);
$total_pages = ceil(count($popular_pages) / $items_per_page);
$pages_offset = ($pages_current_page - 1) * $items_per_page;
$pages_to_display = array_slice($popular_pages, $pages_offset, $items_per_page);

$visitor_countries = alpack_get_visitor_countries(50, $period_days, $start_date, $end_date);
$total_countries = ceil(count($visitor_countries) / $items_per_page);
$countries_offset = ($countries_current_page - 1) * $items_per_page;
$countries_to_display = array_slice($visitor_countries, $countries_offset, $items_per_page);

$referrers = alpack_get_referrers(30, $period_days, $start_date, $end_date);
$total_referrers = ceil(count($referrers) / $items_per_page);
$referrers_offset = ($referrers_current_page - 1) * $items_per_page;
$referrers_to_display = array_slice($referrers, $referrers_offset, $items_per_page);

$total_keywords_count = alpack_get_total_organic_keywords_count($period_days, $start_date, $end_date);
$total_keywords_pages = ceil($total_keywords_count / $keywords_per_page);
$keywords_offset = ($keywords_current_page - 1) * $keywords_per_page;
$organic_keywords = alpack_get_organic_keywords($keywords_per_page, $period_days, $keywords_offset, $start_date, $end_date);

$chart_data = alpack_get_chart_data($chart_period, $start_date, $end_date);

$GLOBALS['presslearn_chart_data'] = $chart_data;
$GLOBALS['presslearn_chart_period'] = $chart_period;
$GLOBALS['presslearn_referrers_to_display'] = $referrers_to_display;

?>
<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>씬 애널리틱스</h1>
    </div>
    <div class="presslearn-header-status">
        <?php if ($analytics_enabled === 'yes'): ?>
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

    <div id="presslearn-banner-container" class="presslearn-banner-container" style="margin-left: 0; margin-right: 0;">
        <div id="presslearn-banner" class="presslearn-banner">
            <a href="#" id="presslearn-banner-link" target="_blank" class="presslearn-banner-link">
                <img src="" id="presslearn-banner-image" class="presslearn-banner-image">
            </a>
        </div>
        <div id="presslearn-banner-skeleton" class="presslearn-banner-skeleton">
            <div class="presslearn-banner-skeleton-content"></div>
        </div>
    </div>

    <div class="presslearn-breadcrumbs-wrap">
        <div class="presslearn-breadcrumbs">
            <span>대시보드</span>
            <span class="divider">/</span>
            <span class="active">씬 애널리틱스</span>
        </div>
    </div>
    
    <?php if (isset($settings_updated) && $settings_updated): ?>
    <div class="notice notice-success inline">
        <p>설정 정보가 성공적으로 저장되었습니다.</p>
        </div>
    <?php endif; ?>

    <div class="presslearn-tabs">
        <a href="?page=presslearn-analytics&tab=settings" class="tab-link <?php echo esc_attr($current_tab === 'settings' ? 'active' : ''); ?>">설정</a>
        <a href="?page=presslearn-analytics&tab=statistics" class="tab-link <?php echo esc_attr($current_tab === 'statistics' ? 'active' : ''); ?>">통계</a>
    </div>
    
    <?php if ($current_tab === 'settings'): ?>
    
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>씬 애널리틱스</h2>
            <p>페이지 별 애널리틱스 기능이 포함되어 있는 경량 애널리틱스 기능입니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>플러그인 기능 활성화</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="analytics-toggle-form">
                        <?php wp_nonce_field('presslearn_toggle_analytics_nonce'); ?>
                        <input type="hidden" name="presslearn_toggle_analytics" value="1">
                        <input type="hidden" name="enable" id="analytics-enable-value" value="<?php echo esc_attr($analytics_enabled === 'yes' ? 'no' : 'yes'); ?>">
                        
                        <label class="switch">
                            <input type="checkbox" <?php echo esc_attr($analytics_enabled === 'yes' ? 'checked' : ''); ?> onchange="document.getElementById('analytics-enable-value').value = this.checked ? 'yes' : 'no'; document.getElementById('analytics-toggle-form').submit();">
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>CloudFlare 사용</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <form method="post" action="" id="analytics-settings-form">
                        <?php wp_nonce_field('presslearn_save_analytics_settings_nonce'); ?>
                        <input type="hidden" name="save_analytics_settings" value="1">
                        
                        <label class="switch">
                            <input type="checkbox" name="use_cloudflare" <?php echo esc_attr($use_cloudflare === 'yes' ? 'checked' : ''); ?>>
                            <span class="slider round"></span>
                        </label>
                    </form>
                </div>
            </div>
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>관리자 IP 통계 제외</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <input type="text" class="regular-text" name="analytics_exclude_admin" form="analytics-settings-form" value="<?php echo esc_attr($analytics_exclude_admin); ?>" placeholder="예: 127.0.0.1, 192.168.0.1">
                </div>
            </div>
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>캐싱 시간 설정</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <select name="analytics_cache_time" form="analytics-settings-form">
                        <option value="60" <?php selected($analytics_cache_time, 60); ?>>1분</option>
                        <option value="300" <?php selected($analytics_cache_time, 300); ?>>5분</option>
                        <option value="600" <?php selected($analytics_cache_time, 600); ?>>10분</option>
                        <option value="1800" <?php selected($analytics_cache_time, 1800); ?>>30분</option>
                        <option value="3600" <?php selected($analytics_cache_time, 3600); ?>>1시간</option>
                        <option value="3600" <?php selected($analytics_cache_time, 7200); ?>>2시간</option>
                        <option value="3600" <?php selected($analytics_cache_time, 14400); ?>>4시간</option>
                        <option value="86400" <?php selected($analytics_cache_time, 86400); ?>>1일</option>
                    </select>
                </div>
            </div>
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item grid-left">
                    <h3>통계 데이터 삭제</h3>
                </div>
                <div class="presslearn-card-body-row-item grid-right">
                    <button type="button" class="negative-btn" id="delete-analytics-btn">통계 데이터 삭제</button>
                </div>
            </div>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">방문자가 많을 경우 캐싱 시간을 늘려야 서버에 부하가 적습니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <button type="submit" class="point-btn" form="analytics-settings-form">저장하기</button>
            </div>
        </div>
    </div>
    
    <?php else: ?>
    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>방문자 통계</h2>
            <p>사이트 방문자 및 페이지뷰 통계를 확인할 수 있습니다.</p>
        </div>
        <div class="presslearn-card-body">
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <h3 style="margin-bottom: 15px;">기간별 요약 (차트)</h3>
                    
                    <div class="chart-period-tabs">
                        <?php
                        $date_params = '';
                        if ($start_date && $end_date) {
                            $date_params = '&start_date=' . esc_attr($start_date) . '&end_date=' . esc_attr($end_date);
                        }
                        
                        $page_params = '';
                        if ($pages_page > 1) {
                            $page_params .= '&pages_page=' . esc_attr($pages_page);
                        }
                        if ($countries_page > 1) {
                            $page_params .= '&countries_page=' . esc_attr($countries_page);
                        }
                        if ($referrers_page > 1) {
                            $page_params .= '&referrers_page=' . esc_attr($referrers_page);
                        }
                        if ($keywords_page > 1) {
                            $page_params .= '&keywords_page=' . esc_attr($keywords_page);
                        }
                        ?>
                        <div class="period-buttons">
                            <a href="?page=presslearn-analytics&tab=statistics&chart_period=daily<?php echo esc_attr($page_params) . esc_attr($date_params); ?>" class="chart-tab <?php echo esc_attr($chart_period === 'daily' ? 'active' : ''); ?>">일간</a>
                            <a href="?page=presslearn-analytics&tab=statistics&chart_period=weekly<?php echo esc_attr($page_params) . esc_attr($date_params); ?>" class="chart-tab <?php echo esc_attr($chart_period === 'weekly' ? 'active' : ''); ?>">주간</a>
                            <a href="?page=presslearn-analytics&tab=statistics&chart_period=yearly<?php echo esc_attr($page_params) . esc_attr($date_params); ?>" class="chart-tab <?php echo esc_attr($chart_period === 'yearly' ? 'active' : ''); ?>">연간</a>
                            <button type="button" class="chart-tab" id="today-btn">오늘</button>
                        </div>
                        
                        <div class="date-range-picker-container">
                            <form id="date-range-form" method="get" action="">
                                <input type="hidden" name="page" value="presslearn-analytics">
                                <input type="hidden" name="tab" value="statistics">
                                <input type="hidden" name="chart_period" value="<?php echo esc_attr($chart_period); ?>">
                                
                                <?php
                                if ($pages_page > 1) {
                                    echo '<input type="hidden" name="pages_page" value="' . esc_attr($pages_page) . '">';
                                }
                                if ($countries_page > 1) {
                                    echo '<input type="hidden" name="countries_page" value="' . esc_attr($countries_page) . '">';
                                }
                                if ($referrers_page > 1) {
                                    echo '<input type="hidden" name="referrers_page" value="' . esc_attr($referrers_page) . '">';
                                }
                                if ($keywords_page > 1) {
                                    echo '<input type="hidden" name="keywords_page" value="' . esc_attr($keywords_page) . '">';
                                }
                                ?>
                                
                                <div class="date-inputs">
                                    <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>" max="<?php echo esc_attr($end_date); ?>">
                                    <span>~</span>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>" min="<?php echo esc_attr($start_date); ?>" max="<?php echo esc_attr(gmdate('Y-m-d')); ?>">
                                    <button type="submit" class="point-btn">적용</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="chart-container" style="position: relative; height: 400px; width: 100%; max-width: 100%; overflow: hidden; box-sizing: border-box; padding-top: 30px; margin-top: 10px;">
                        <canvas id="analyticsChart" style="max-width: 100%;"></canvas>
                    </div>

                    <?php
                    add_action('admin_footer', function() use ($chart_data, $chart_period, $referrers_to_display, $current_tab) {
                        if ($current_tab === 'statistics') {
                            presslearn_render_analytics_scripts($chart_data, $chart_period, $referrers_to_display);
                        }
                    });
                    ?>
                </div>
            </div>

            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <h3 style="margin-bottom: 15px;">기간별 요약</h3>
                    
                    <div class="stats-summary">
                        <div class="stat-item">
                            <h3><?php echo ($start_date && $end_date) ? ($start_date == gmdate('Y-m-d') && $end_date == gmdate('Y-m-d') ? esc_html('오늘') : esc_html(date_i18n('Y년 m월 d일', strtotime($start_date))) . ' ~ ' . esc_html(date_i18n('Y년 m월 d일', strtotime($end_date)))) : esc_html('오늘'); ?></h3>
                            <div class="stat-value"><?php echo esc_html(number_format($dummy_stats['today']['pageviews'])); ?></div>
                            <p>페이지뷰</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo ($start_date && $end_date) ? ($start_date == gmdate('Y-m-d') && $end_date == gmdate('Y-m-d') ? esc_html('오늘') : esc_html(date_i18n('Y년 m월 d일', strtotime($start_date))) . ' ~ ' . esc_html(date_i18n('Y년 m월 d일', strtotime($end_date)))) : esc_html('오늘'); ?></h3>
                            <div class="stat-value"><?php echo esc_html(number_format($dummy_stats['today']['ip_visitors'])); ?></div>
                            <p>순 방문자 (IP 기준)</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo ($start_date && $end_date) ? ($start_date == gmdate('Y-m-d') && $end_date == gmdate('Y-m-d') ? esc_html('오늘') : esc_html(date_i18n('Y년 m월 d일', strtotime($start_date))) . ' ~ ' . esc_html(date_i18n('Y년 m월 d일', strtotime($end_date)))) : esc_html('오늘'); ?></h3>
                            <div class="stat-value"><?php echo esc_html(number_format($dummy_stats['today']['visitors'])); ?></div>
                            <p>방문자 (브라우저 기준)</p>
                        </div>
                        <div class="stat-item">
                            <h3>최근 7일</h3>
                            <div class="stat-value"><?php echo esc_html(number_format($dummy_stats['week']['ip_visitors'])); ?></div>
                            <p>순 방문자 (IP 기준)</p>
                        </div>
                        <div class="stat-item">
                            <h3>최근 30일</h3>
                            <div class="stat-value"><?php echo esc_html(number_format($dummy_stats['month']['ip_visitors'])); ?></div>
                            <p>순 방문자 (IP 기준)</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <h3 style="margin-bottom: 15px;">인기 페이지 (<?php echo esc_html(count($popular_pages)); ?>)</h3>
                    
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>페이지</th>
                                <th width="20%">조회수</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages_to_display as $page): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url($page['url']); ?>" target="_blank">
                                        <?php echo esc_html($page['title']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html(number_format($page['views'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php 
                                $pagination_base_url = "?page=presslearn-analytics&tab=statistics&chart_period={$chart_period}";
                                
                                if ($start_date && $end_date) {
                                    $pagination_base_url .= "&start_date=" . esc_attr($start_date) . "&end_date=" . esc_attr($end_date);
                                }
                                
                                if ($pages_page > 1) {
                                    $pagination_base_url .= "&pages_page=" . esc_attr($pages_page);
                                }
                                if ($countries_page > 1) {
                                    $pagination_base_url .= "&countries_page=" . esc_attr($countries_page);
                                }
                                if ($referrers_page > 1) {
                                    $pagination_base_url .= "&referrers_page=" . esc_attr($referrers_page);
                                }
                                if ($keywords_page > 1) {
                                    $pagination_base_url .= "&keywords_page=" . esc_attr($keywords_page);
                                }
                                
                                if ($pages_current_page > 1):
                                ?>
                                    <a class="first-page button" href="<?php echo esc_url($pagination_base_url); ?>&pages_page=1"><span aria-hidden="true">«</span></a>
                                    <a class="prev-page button" href="<?php echo esc_url($pagination_base_url); ?>&pages_page=<?php echo esc_attr(max(1, $pages_current_page - 1)); ?>"><span aria-hidden="true">‹</span></a>
                                <?php endif; ?>
                                
                                <span class="pagination-current"><?php echo esc_html($pages_current_page); ?></span> / <span class="pagination-total"><?php echo esc_html($total_pages); ?></span>
                                
                                <?php if ($pages_current_page < $total_pages): ?>
                                    <a class="next-page button" href="<?php echo esc_url($pagination_base_url); ?>&pages_page=<?php echo esc_attr(max(1, $pages_current_page + 1)); ?>"><span aria-hidden="true">›</span></a>
                                    <a class="last-page button" href="<?php echo esc_url($pagination_base_url); ?>&pages_page=<?php echo esc_attr(max(1, $total_pages)); ?>"><span aria-hidden="true">»</span></a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="presslearn-card-body-row">  
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <h3 style="margin-bottom: 15px;">유입 경로</h3>
                    
                    <div class="referrer-chart-container">
                        <canvas id="referrerChart"></canvas>
                    </div>

                    <table class="widefat referrers-table">
                        <thead>
                            <tr>
                                <th width="40px"></th>
                                <th>경로</th>
                                <th width="20%">페이지뷰(PV)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrers_to_display as $index => $referrer): ?>
                            <tr class="referrer-row">
                                <td>
                                    <?php if (!empty($referrer['details'])): ?>
                                    <button type="button" class="toggle-details" data-index="<?php echo esc_attr($index); ?>">
                                        <span class="dashicons dashicons-plus"></span>
                                    </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($referrer['referrer']); ?>
                                    <?php 
                                    if (isset($referrer['type']) && $referrer['type'] === '자연 유입') {
                                        echo '<span class="organic-referrer-badge">' . esc_html('자연 유입') . '</span>';
                                    } 
                                    elseif (isset($referrer['type']) && $referrer['type'] === '외부 유입') {
                                        echo '<span class="external-referrer-badge">' . esc_html('외부 유입') . '</span>';
                                    }
                                    elseif ($referrer['referrer'] !== '직접 접속' && 
                                        $referrer['referrer'] !== '데이터 없음') {
                                        echo '<span class="external-referrer-badge">' . esc_html('외부 유입') . '</span>';
                                    }
                                    ?>
                                </td>
                                <td><?php echo esc_html(number_format($referrer['count'])); ?></td>
                            </tr>
                            <?php if (!empty($referrer['details'])): ?>
                            <tr class="referrer-details" id="details-<?php echo esc_attr($index); ?>" style="display: none;">
                                <td colspan="3">
                                    <div class="referrer-details-inner">
                                        <ul class="referrer-details-list">
                                            <?php foreach ($referrer['details'] as $detail): ?>
                                            <li>
                                                <?php
                                                $url = $detail['url'];
                                                if ($url !== '북마크 및 직접 URL 입력' && $url !== '기타 알 수 없는 소스' && strpos($url, 'http') !== 0) {
                                                    $url = 'https://' . $url;
                                                }
                                                
                                                if ($url === '북마크 및 직접 URL 입력' || $url === '기타 알 수 없는 소스'):
                                                ?>
                                                <div class="referrer-url-wrap">
                                                    <span class="referrer-url"><?php echo esc_html($detail['url']); ?></span>
                                                    <span class="referrer-count"><?php echo esc_html(number_format($detail['count'])); ?></span>
                                                </div>
                                                <?php else: ?>
                                                <div class="referrer-url-wrap">
                                                    <span class="referrer-url">
                                                <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener">
                                                    <?php echo esc_html($detail['url']); ?>
                                                </a> 
                                                    </span>
                                                    <span class="referrer-count"><?php echo esc_html(number_format($detail['count'])); ?></span>
                                                </div>
                                                <?php endif; ?>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_referrers > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php 
                                $pagination_base_url = "?page=presslearn-analytics&tab=statistics&chart_period={$chart_period}";
                                
                                if ($start_date && $end_date) {
                                    $pagination_base_url .= "&start_date=" . esc_attr($start_date) . "&end_date=" . esc_attr($end_date);
                                }
                                
                                if ($pages_page > 1) {
                                    $pagination_base_url .= "&pages_page=" . esc_attr($pages_page);
                                }
                                if ($countries_page > 1) {
                                    $pagination_base_url .= "&countries_page=" . esc_attr($countries_page);
                                }
                                if ($referrers_page > 1) {
                                    $pagination_base_url .= "&referrers_page=" . esc_attr($referrers_page);
                                }
                                if ($keywords_page > 1) {
                                    $pagination_base_url .= "&keywords_page=" . esc_attr($keywords_page);
                                }
                                
                                if ($referrers_current_page > 1): 
                                ?>
                                    <a class="first-page button" href="<?php echo esc_url($pagination_base_url); ?>&referrers_page=1"><span aria-hidden="true">«</span></a>
                                    <a class="prev-page button" href="<?php echo esc_url($pagination_base_url); ?>&referrers_page=<?php echo esc_attr(max(1, $referrers_current_page - 1)); ?>"><span aria-hidden="true">‹</span></a>
                                <?php endif; ?>
                                
                                <span class="pagination-current"><?php echo esc_html($referrers_current_page); ?></span> / <span class="pagination-total"><?php echo esc_html($total_referrers); ?></span>
                                
                                <?php if ($referrers_current_page < $total_referrers): ?>
                                    <a class="next-page button" href="<?php echo esc_url($pagination_base_url); ?>&referrers_page=<?php echo esc_attr(max(1, $referrers_current_page + 1)); ?>"><span aria-hidden="true">›</span></a>
                                    <a class="last-page button" href="<?php echo esc_url($pagination_base_url); ?>&referrers_page=<?php echo esc_attr(max(1, $total_referrers)); ?>"><span aria-hidden="true">»</span></a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <h3 style="margin-bottom: 15px;">자연 유입 키워드 (<?php echo esc_html($total_keywords_count); ?>)</h3>
                    
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>키워드</th>
                                <th>검색 엔진</th>
                                <th width="20%">조회수</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($organic_keywords as $keyword): ?>
                            <tr>
                                <td>
                                    <?php if ($keyword['keyword'] !== '(not provided)' && $keyword['keyword'] !== '아직 수집된 키워드가 없습니다'): ?>
                                        <a href="<?php echo esc_url($keyword['referrer'] ?? ($keyword['search_engine'] === '네이버' ? 'https://search.naver.com/search.naver?query=' . urlencode($keyword['keyword']) : ($keyword['search_engine'] === '구글' ? 'https://www.google.com/search?q=' . urlencode($keyword['keyword']) : ($keyword['search_engine'] === '다음' ? 'https://search.daum.net/search?w=tot&q=' . urlencode($keyword['keyword']) : ($keyword['search_engine'] === '빙' ? 'https://www.bing.com/search?q=' . urlencode($keyword['keyword']) : 'https://search.yahoo.com/search?p=' . urlencode($keyword['keyword'])))))); ?>" target="_blank" rel="noopener">
                                            <?php echo esc_html($keyword['keyword']); ?>
                                        </a>
                                    <?php else: ?>
                                        <?php echo esc_html($keyword['keyword']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($keyword['search_engine_display']); ?>
                                    <?php if ($keyword['search_engine'] === '네이버'): ?>
                                        <span class="organic-referrer-badge"><?php echo esc_html('네이버'); ?></span>
                                    <?php elseif ($keyword['search_engine'] === '구글'): ?>
                                        <span class="external-referrer-badge"><?php echo esc_html('구글'); ?></span>
                                    <?php elseif ($keyword['search_engine'] === '다음'): ?>
                                        <span class="daum-referrer-badge"><?php echo esc_html('다음'); ?></span>
                                    <?php elseif ($keyword['search_engine'] === '빙'): ?>
                                        <span class="bing-referrer-badge"><?php echo esc_html('빙'); ?></span>
                                    <?php elseif ($keyword['search_engine'] === '야후'): ?>
                                        <span class="external-referrer-badge"><?php echo esc_html('야후'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html(number_format($keyword['count'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_keywords_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php 
                                $pagination_base_url = "?page=presslearn-analytics&tab=statistics&chart_period={$chart_period}";
                                
                                if ($start_date && $end_date) {
                                    $pagination_base_url .= "&start_date=" . esc_attr($start_date) . "&end_date=" . esc_attr($end_date);
                                }
                                
                                if ($pages_page > 1) {
                                    $pagination_base_url .= "&pages_page=" . esc_attr($pages_page);
                                }
                                if ($countries_page > 1) {
                                    $pagination_base_url .= "&countries_page=" . esc_attr($countries_page);
                                }
                                if ($referrers_page > 1) {
                                    $pagination_base_url .= "&referrers_page=" . esc_attr($referrers_page);
                                }
                                
                                if ($keywords_current_page > 1): 
                                ?>
                                    <a class="first-page button" href="<?php echo esc_url($pagination_base_url); ?>&keywords_page=1"><span aria-hidden="true">«</span></a>
                                    <a class="prev-page button" href="<?php echo esc_url($pagination_base_url); ?>&keywords_page=<?php echo esc_attr(max(1, $keywords_current_page - 1)); ?>"><span aria-hidden="true">‹</span></a>
                                <?php endif; ?>
                                
                                <span class="pagination-current"><?php echo esc_html($keywords_current_page); ?></span> / <span class="pagination-total"><?php echo esc_html($total_keywords_pages); ?></span>
                                
                                <?php if ($keywords_current_page < $total_keywords_pages): ?>
                                    <a class="next-page button" href="<?php echo esc_url($pagination_base_url); ?>&keywords_page=<?php echo esc_attr(max(1, $keywords_current_page + 1)); ?>"><span aria-hidden="true">›</span></a>
                                    <a class="last-page button" href="<?php echo esc_url($pagination_base_url); ?>&keywords_page=<?php echo esc_attr(max(1, $total_keywords_pages)); ?>"><span aria-hidden="true">»</span></a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="presslearn-card-body-row">
                <div class="presslearn-card-body-row-item" style="flex: 100%;">
                    <h3 style="margin-bottom: 15px;">방문자 국가</h3>
                    
                    <table class="widefat">
                        <thead>
                            <tr>
                                <th>국가</th>
                                <th width="20%">페이지뷰(PV)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($countries_to_display as $country): ?>
                            <tr>
                                <td><?php echo esc_html($country['country']); ?></td>
                                <td><?php echo esc_html(number_format($country['count'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($total_countries > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php 
                                $pagination_base_url = "?page=presslearn-analytics&tab=statistics&chart_period={$chart_period}";
                                
                                if ($start_date && $end_date) {
                                    $pagination_base_url .= "&start_date=" . esc_attr($start_date) . "&end_date=" . esc_attr($end_date);
                                }
                                
                                if ($pages_page > 1) {
                                    $pagination_base_url .= "&pages_page=" . esc_attr($pages_page);
                                }
                                if ($referrers_page > 1) {
                                    $pagination_base_url .= "&referrers_page=" . esc_attr($referrers_page);
                                }
                                if ($keywords_page > 1) {
                                    $pagination_base_url .= "&keywords_page=" . esc_attr($keywords_page);
                                }
                                
                                if ($countries_current_page > 1): 
                                ?>
                                    <a class="first-page button" href="<?php echo esc_url($pagination_base_url); ?>&countries_page=1"><span aria-hidden="true">«</span></a>
                                    <a class="prev-page button" href="<?php echo esc_url($pagination_base_url); ?>&countries_page=<?php echo esc_attr(max(1, $countries_current_page - 1)); ?>"><span aria-hidden="true">‹</span></a>
                                <?php endif; ?>
                                
                                <span class="pagination-current"><?php echo esc_html($countries_current_page); ?></span> / <span class="pagination-total"><?php echo esc_html($total_countries); ?></span>
                                
                                <?php if ($countries_current_page < $total_countries): ?>
                                    <a class="next-page button" href="<?php echo esc_url($pagination_base_url); ?>&countries_page=<?php echo esc_attr(max(1, $countries_current_page + 1)); ?>"><span aria-hidden="true">›</span></a>
                                    <a class="last-page button" href="<?php echo esc_url($pagination_base_url); ?>&countries_page=<?php echo esc_attr(max(1, $total_countries)); ?>"><span aria-hidden="true">»</span></a>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">통계 데이터는 설정한 캐싱 기준으로 업데이트됩니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <?php if ($analytics_enabled !== 'yes'): ?>
                <p style="color: #d32f2f;">통계 기능을 사용하려면 먼저 <a href="?page=presslearn-analytics&tab=settings">설정</a>에서 기능을 활성화하세요.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div> 

