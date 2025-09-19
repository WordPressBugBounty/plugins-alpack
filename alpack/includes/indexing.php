<?php
if (!defined('ABSPATH')) {
    exit;
}

class PressLearn_IndexNow {
    
    private static $instance = null;
    private $api_endpoint = 'https://searchadvisor.naver.com/indexnow';
    private $api_key = '';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('save_post', array($this, 'auto_index_post'), 10, 3);
        add_action('presslearn_index_post_delayed', array($this, 'index_post_delayed'), 10, 4);
        add_action('wp_ajax_presslearn_manual_index', array($this, 'manual_index_ajax'));

        add_action('wp_ajax_presslearn_bulk_index', array($this, 'bulk_index_ajax'));
    }
    
    public function init() {
        $this->api_key = get_option('presslearn_indexnow_api_key', '');
        $this->create_indexing_table();
    }
    
    private function create_indexing_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'presslearn_indexing_logs';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            post_url text NOT NULL,
            post_title text NOT NULL,
            request_type varchar(50) NOT NULL DEFAULT 'auto',
            response_code int(11) DEFAULT NULL,
            response_message text DEFAULT NULL,
            indexed_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY indexed_at (indexed_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public function auto_index_post($post_id, $post, $update) {
        if (get_option('presslearn_auto_index_enabled', 'no') !== 'yes') {
            return;
        }
        
        if (get_option('presslearn_auto_indexing_enabled', 'yes') !== 'yes') {
            return;
        }
        
        if ($post->post_status !== 'publish') {
            return;
        }
        
        if (!in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        $post_url = get_permalink($post_id);
        if (!$post_url) {
            return;
        }
        
        wp_schedule_single_event(time() + 10, 'presslearn_index_post_delayed', array($post_id, $post_url, $post->post_title, 'auto'));
    }
    
    public function index_post_delayed($post_id, $post_url, $post_title, $request_type) {
        $this->submit_to_indexnow($post_id, $post_url, $post_title, $request_type);
    }
    
    public function submit_to_indexnow($post_id, $post_url, $post_title, $request_type = 'manual') {
        if (empty($this->api_key)) {
            $this->log_indexing_result($post_id, $post_url, $post_title, $request_type, 400, 'API 키가 설정되지 않았습니다.');
            return false;
        }
        
        $body = array(
            'host' => parse_url(home_url(), PHP_URL_HOST),
            'key' => $this->api_key,
            'urlList' => array($post_url)
        );
        
        $response = wp_remote_post($this->api_endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/json; charset=utf-8',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . ' PressLearn IndexNow'
            ),
            'body' => wp_json_encode($body),
            'timeout' => 30,
            'method' => 'POST'
        ));
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->log_indexing_result($post_id, $post_url, $post_title, $request_type, 0, $error_message);
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        $success = in_array($response_code, array(200, 202));
        
        $message = $success ? '인덱싱 요청 성공' : '인덱싱 요청 실패: ' . $response_body;
        
        $this->log_indexing_result($post_id, $post_url, $post_title, $request_type, $response_code, $message);
        
        return $success;
    }
    
    private function log_indexing_result($post_id, $post_url, $post_title, $request_type, $response_code, $response_message) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'presslearn_indexing_logs';
        
        $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'post_url' => $post_url,
                'post_title' => $post_title,
                'request_type' => $request_type,
                'response_code' => $response_code,
                'response_message' => $response_message,
                'indexed_at' => current_time('mysql')
            ),
            array(
                '%d', '%s', '%s', '%s', '%d', '%s', '%s'
            )
        );
    }
    
    public function manual_index_ajax() {
        check_ajax_referer('presslearn_indexing_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
            return;
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => '유효하지 않은 게시글 ID입니다.'));
            return;
        }
        
        $post = get_post($post_id);
        if (!$post || $post->post_status !== 'publish') {
            wp_send_json_error(array('message' => '발행된 게시글을 찾을 수 없습니다.'));
            return;
        }
        
        $post_url = get_permalink($post_id);
        if (!$post_url) {
            wp_send_json_error(array('message' => '게시글 URL을 가져올 수 없습니다.'));
            return;
        }
        
        $result = $this->submit_to_indexnow($post_id, $post_url, $post->post_title, 'manual');
        
        if ($result) {
            wp_send_json_success(array('message' => '인덱싱 요청이 성공적으로 전송되었습니다.'));
        } else {
            wp_send_json_error(array('message' => '인덱싱 요청 전송에 실패했습니다.'));
        }
    }
    
    public function bulk_index_ajax() {
        check_ajax_referer('presslearn_indexing_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => '권한이 없습니다.'));
            return;
        }
        
        $post_ids = isset($_POST['post_ids']) ? array_map('intval', $_POST['post_ids']) : array();
        
        if (empty($post_ids)) {
            wp_send_json_error(array('message' => '선택된 게시글이 없습니다.'));
            return;
        }
        
        $success_count = 0;
        $total_count = count($post_ids);
        
        if ($total_count > 50) {
            wp_send_json_error(array('message' => '한 번에 최대 50개까지만 인덱싱할 수 있습니다.'));
            return;
        }
        
        foreach ($post_ids as $post_id) {
            $post = get_post($post_id);
            if (!$post || $post->post_status !== 'publish') {
                continue;
            }
            
            $post_url = get_permalink($post_id);
            if (!$post_url) {
                continue;
            }
            
            if ($this->submit_to_indexnow($post_id, $post_url, $post->post_title, 'bulk')) {
                $success_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => "총 {$total_count}개 중 {$success_count}개의 게시글이 성공적으로 인덱싱 요청되었습니다.",
            'success_count' => $success_count,
            'total_count' => $total_count
        ));
    }
    

    

    
    public function validate_api_key($api_key) {
        if (empty($api_key) || !is_string($api_key)) {
            return false;
        }
        
        if (strlen($api_key) < 10 || strlen($api_key) > 64) {
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $api_key)) {
            return false;
        }
        
        return true;
    }
    
    public function create_api_key_file($api_key) {
        if (!$this->validate_api_key($api_key)) {
            return false;
        }
        
        if (!function_exists('WP_Filesystem')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        
        WP_Filesystem();
        global $wp_filesystem;
        
        if (!$wp_filesystem) {
            return false;
        }
        
        $key_file_path = ABSPATH . $api_key . '.txt';
        
        $filename = basename($key_file_path);
        if (!preg_match('/^[a-zA-Z0-9\-]+\.txt$/', $filename) || strlen($filename) > 68) {
            return false;
        }
        
        $result = $wp_filesystem->put_contents($key_file_path, $api_key, FS_CHMOD_FILE);
        
        return $result;
    }
}

function presslearn_indexnow() {
    return PressLearn_IndexNow::get_instance();
}

presslearn_indexnow();
