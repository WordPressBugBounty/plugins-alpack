<?php
/**
 * PressLearn API
 */

if (!defined('ABSPATH')) {
    exit;
}

class PressLearn_API {
 
    public static function init() {
        add_action('rest_api_init', array(__CLASS__, 'register_endpoints'));
        add_action('rest_api_init', array(__CLASS__, 'add_cors_support'));
        add_filter('rest_authentication_errors', array(__CLASS__, 'disable_rest_authentication'), 999);
        add_filter('rest_nonce_enabled', array(__CLASS__, 'disable_nonce_for_presslearn'), 999);
        add_action('rest_api_init', array(__CLASS__, 'remove_cookie_check_for_presslearn'));
    }
    
    public static function disable_rest_authentication($errors) {
        $current_route = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        
        if (strpos($current_route, '/wp-json/presslearn/v1/activate') !== false) {
            return $errors;
        }
        
        if (strpos($current_route, '/wp-json/presslearn/') !== false) {
            if (current_user_can('manage_options') || self::is_trusted_origin()) {
                return true;
            }
        }
        
        return $errors; 
    }
    
    public static function disable_nonce_for_presslearn($enabled) {
        $current_route = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        
        if (strpos($current_route, '/wp-json/presslearn/v1/activate') !== false) {
            return $enabled;
        }
        
        if (strpos($current_route, '/wp-json/presslearn/') !== false) {
            if (current_user_can('manage_options') || self::is_trusted_origin()) {
                return false;
            }
        }
        
        return $enabled;
    }
    
    public static function remove_cookie_check_for_presslearn() {
        add_filter('rest_pre_dispatch', function($result, $server, $request) {
            $route = $request->get_route();
            
            if (strpos($route, '/presslearn/') === 0) {
                remove_filter('rest_pre_dispatch', 'rest_cookie_check_errors', 10);
            }
            
            return $result;
        }, 5, 3);
    }
    
    public static function add_cors_support() {
        add_filter('rest_pre_serve_request', function($served, $result, $request) {
            $route = $request->get_route();
            
            if (strpos($route, '/presslearn/') === 0) {
                $origin = get_http_origin();
                if ($origin) {
                    header('Access-Control-Allow-Origin: ' . esc_url_raw($origin));
                    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
                    header('Access-Control-Allow-Headers: Content-Type, Authorization');
                    header('Access-Control-Allow-Credentials: true');
                }
                
                if (isset($_SERVER['REQUEST_METHOD']) && sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) === 'OPTIONS') {
                    status_header(200);
                    return true;
                }
            }
            
            return $served;
        }, 10, 3);
    }

    public static function register_endpoints() {
        register_rest_route('presslearn/v1', '/activate', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'activate_plugin'),
            'permission_callback' => array(__CLASS__, 'check_activate_permission'), 
        ));
        
        register_rest_route('presslearn/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'check_status'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));

        register_rest_route('presslearn/v1', '/banner', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_banner'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
        
        register_rest_route('presslearn/v1', '/notice', array(
            'methods' => 'GET',
            'callback' => array(__CLASS__, 'get_latest_notice'),
            'permission_callback' => array(__CLASS__, 'check_permission'),
        ));
    }

    public static function check_permission($request) {
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $site_host = wp_parse_url(site_url(), PHP_URL_HOST);
        $origin = get_http_origin();
        $origin_host = $origin ? wp_parse_url($origin, PHP_URL_HOST) : '';
        
        if ($origin_host && $origin_host === $site_host) {
            return true;
        }
        
        $current_host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';
        if ($current_host === $site_host) {
            return true;
        }
        
        return false;
    }

    public static function check_admin_permission($request) {
        return self::check_permission($request);
    }
    
    private static function is_trusted_origin() {
        $origin = get_http_origin();
        $allowed_domains = array('qa.ledu.kr', 'api.qa.ledu.kr', 'presslearn.co.kr');
        
        if (!empty($origin)) {
            $origin_host = wp_parse_url($origin, PHP_URL_HOST);
            if ($origin_host && in_array($origin_host, $allowed_domains, true)) {
                return true;
            }
        }
        
        $site_host = wp_parse_url(site_url(), PHP_URL_HOST);
        $origin_host = $origin ? wp_parse_url($origin, PHP_URL_HOST) : '';
        
        return $origin_host === $site_host;
    }
    
    private static function validate_trusted_domain_request($request, $origin_host) {
        
        $key = $request->get_param('key');
        if (empty($key)) {
            error_log("PressLearn: Activation request from {$origin_host} without key");
            return false;
        }
        
        if (strlen($key) < 32) {
            error_log("PressLearn: Activation request from {$origin_host} with invalid key format");
            return false;
        }
        
        $recent_attempts = get_transient('presslearn_activation_attempts_' . md5($origin_host));
        if ($recent_attempts && $recent_attempts > 5) {
            error_log("PressLearn: Too many activation attempts from {$origin_host}");
            return false;
        }
        
        set_transient('presslearn_activation_attempts_' . md5($origin_host), ($recent_attempts + 1), 300); // 5분
        
        $user_agent = $request->get_header('User-Agent');
        if (empty($user_agent) || strlen($user_agent) < 10) {
            error_log("PressLearn: Suspicious User-Agent from {$origin_host}: " . $user_agent);
            return false;
        }
        
        error_log("PressLearn: Valid activation request from trusted domain: {$origin_host}");
        return true;
    }
    
    public static function check_activate_permission($request) {
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $origin = get_http_origin();
        $allowed_domains = array('qa.ledu.kr', 'api.qa.ledu.kr', 'presslearn.co.kr');
        $origin_host = $origin ? wp_parse_url($origin, PHP_URL_HOST) : '';
        
        if (!empty($origin_host) && in_array($origin_host, $allowed_domains, true)) {
            return self::validate_trusted_domain_request($request, $origin_host);
        }
        
        $site_host = wp_parse_url(site_url(), PHP_URL_HOST);
        if ($origin_host === $site_host) {
            $nonce = $request->get_header('X-WP-Nonce');
            if ((!empty($nonce) && wp_verify_nonce($nonce, 'wp_rest')) || is_user_logged_in()) {
                return true;
            }
        }
        
        return false;
    }

    public static function activate_plugin($request) {
        $key = $request->get_param('key');
        $origin = get_http_origin();
        
        error_log("PressLearn: Activation attempt - Origin: {$origin}, Key length: " . strlen($key ?: ''));
        
        if (empty($key)) {
            error_log("PressLearn: Activation failed - Empty key from origin: {$origin}");
            return new WP_Error(
                'invalid_key',
                '유효하지 않은 키입니다.',
                array('status' => 400)
            );
        }
        
        $is_valid = self::validate_key($key);
        
        if (!$is_valid) {
            error_log("PressLearn: Activation failed - Invalid key from origin: {$origin}, Key: " . substr($key, 0, 8) . '...');
            return new WP_Error(
                'invalid_key',
                '키가 유효하지 않습니다.',
                array('status' => 400)
            );
        }
        
        update_option('presslearn_plugin_key', $key);
        
        self::perform_activation_tasks($key);
        
        error_log("PressLearn: Activation successful from origin: {$origin}");
        
        return array(
            'success' => true,
            'message' => 'Successfully activated.',
            'timestamp' => current_time('timestamp'),
            'debug' => array(
                'origin' => $origin,
                'key_length' => strlen($key),
                'is_admin' => current_user_can('manage_options'),
                'user_logged_in' => is_user_logged_in()
            )
        );
    }
    
    public static function check_status($request) {
        $key = get_option('presslearn_plugin_key', '');
        $is_active = !empty($key);
        
        $activated_time = get_option('presslearn_plugin_activated_time', 0);
        
        return array(
            'is_active' => $is_active,
            'activated_time' => $activated_time,
            'message' => $is_active ? '플러그인이 활성화되었습니다.' : '플러그인이 비활성화 상태입니다.'
        );
    }
    
    private static function validate_key($key) {
        $start_time = microtime(true);
        
        $is_empty = empty($key);
        $is_short = strlen($key ?: '') < 32;
        
        $server_valid = self::mock_validate_with_presslearn_server($key ?: '');
        
        $is_valid = !$is_empty && !$is_short && $server_valid;
        
        $min_execution_time = 0.1;
        $elapsed = microtime(true) - $start_time;
        if ($elapsed < $min_execution_time) {
            usleep(($min_execution_time - $elapsed) * 1000000);
        }
        
        return $is_valid;
    }
    
    private static function mock_validate_with_presslearn_server($key) {
        $origin = get_http_origin();
        $origin_host = $origin ? wp_parse_url($origin, PHP_URL_HOST) : '';
        
        if (empty($key) || strlen($key) < 32) {
            error_log("PressLearn: Key validation failed - Invalid format from {$origin_host}");
            return false;
        }
        
        if (strpos($key, 'PL_') === 0) {
            error_log("PressLearn: Key validation success - Test key from {$origin_host}");
            return true;
        }
        
        $trusted_domains = array('qa.ledu.kr', 'api.qa.ledu.kr', 'presslearn.co.kr');
        if (in_array($origin_host, $trusted_domains, true)) {
            error_log("PressLearn: Key validation success - Trusted domain {$origin_host}");
            return true;
        }
        
        if (current_user_can('manage_options')) {
            error_log('PressLearn: Key validation success - Admin activated from ' . $origin_host . ' with key: ' . substr($key, 0, 8) . '...');
            return true;
        }
        
        error_log("PressLearn: Key validation failed - Untrusted source {$origin_host}");
        return false;
    }
    

    private static function perform_activation_tasks($key) {
        update_option('presslearn_plugin_activated_time', time());
        self::log_activation_event($key);
    }

    private static function log_activation_event($key) {
        $masked_key = substr($key, 0, 4) . '...' . substr($key, -4);
        
        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
        $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
        $origin = get_http_origin();
        
        $user_info = 'anonymous';
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $user_info = $current_user->user_login . ' (ID: ' . $current_user->ID . ')';
        }
        
        $log_data = array(
            'time' => current_time('mysql'),
            'key' => $masked_key,
            'ip' => $remote_addr,
            'user_agent' => $user_agent,
            'origin' => $origin,
            'user' => $user_info,
            'is_admin' => current_user_can('manage_options'),
            'method' => 'REST_API'
        );
        
        $activation_logs = get_option('presslearn_activation_logs', array());
        $activation_logs[] = $log_data;
        
        if (count($activation_logs) > 20) {
            $activation_logs = array_slice($activation_logs, -20);
        }
        
        update_option('presslearn_activation_logs', $activation_logs);
        
        error_log('PressLearn Activation: ' . json_encode($log_data));
    }

    public static function get_banner($request) {
        $supabase_url = 'https://odkponsvhcfajgoetubm.supabase.co';
        $supabase_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im9ka3BvbnN2aGNmYWpnb2V0dWJtIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDU4OTIxMjMsImV4cCI6MjA2MTQ2ODEyM30.yR08gaJeSy4kagAT3PZl1i8uAC6aEEnZSfQ4sSbqOYk';
        
        $response = wp_remote_get(
            $supabase_url . '/rest/v1/banner_table?id=eq.1',
            array(
                'headers' => array(
                    'apikey' => $supabase_key,
                    'Authorization' => 'Bearer ' . $supabase_key,
                    'Content-Type' => 'application/json'
                )
            )
        );

        if (is_wp_error($response)) {
            return new WP_Error(
                'banner_fetch_error',
                '배너 데이터를 가져오는데 실패했습니다.',
                array('status' => 500)
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data)) {
            return new WP_Error(
                'no_banner',
                '배너 데이터가 없습니다.',
                array('status' => 404)
            );
        }

        return array(
            'success' => true,
            'data' => $data[0]
        );
    }
    
    public static function get_latest_notice($request) {
        $cache_key = 'presslearn_latest_notice';
        $cached_notice = get_transient($cache_key);
        
        if ($cached_notice !== false) {
            return array(
                'success' => true,
                'data' => $cached_notice
            );
        }
        
        $feed_url = 'https://alpack.dev/category/notice/feed/';
        
        $response = wp_remote_get($feed_url, array(
            'timeout' => 10,
            'user-agent' => 'PressLearn Plugin/' . get_bloginfo('url'),
            'sslverify' => true
        ));
        
        if (is_wp_error($response)) {
            error_log('PressLearn Notice Feed Error: ' . $response->get_error_message());
            return new WP_Error(
                'notice_fetch_error',
                '공지사항을 가져오는데 실패했습니다.',
                array('status' => 500)
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        
        if ($xml === false) {
            return new WP_Error(
                'notice_parse_error',
                '공지사항 데이터 파싱에 실패했습니다.',
                array('status' => 500)
            );
        }
        
        $latest_notice = array();
        if (isset($xml->channel->item[0])) {
            $item = $xml->channel->item[0];
            $latest_notice = array(
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'date' => date('Y-m-d', strtotime((string) $item->pubDate))
            );
            
            set_transient($cache_key, $latest_notice, DAY_IN_SECONDS);
        }
        
        if (empty($latest_notice)) {
            return new WP_Error(
                'no_notice',
                '공지사항이 없습니다.',
                array('status' => 404)
            );
        }
        
        return array(
            'success' => true,
            'data' => $latest_notice
        );
    }
}

PressLearn_API::init(); 