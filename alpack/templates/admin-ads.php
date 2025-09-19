<?php
if (!defined('ABSPATH')) {
    exit;
}

$is_activated = presslearn_plugin()->is_plugin_activated();

if (!$is_activated) {
    wp_redirect(admin_url('admin.php?page=presslearn-settings'));
    exit;
}

if (!function_exists('WP_Filesystem')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}

$filesystem_initialized = WP_Filesystem();
if (!$filesystem_initialized) {
    $filesystem_error = true;
}

$settings_updated = false;
$save_error = false;
$existing_content = '';
$robots_settings_updated = false;
$robots_save_error = false;
$robots_existing_content = '';
$naver_upload_success = false;
$google_upload_success = false;
$upload_error = false;

if (isset($_POST['save_ads_txt_content'])) {
    check_admin_referer('presslearn_save_ads_txt_nonce');
    
    if (current_user_can('manage_options') && $filesystem_initialized) {
        global $wp_filesystem;
        
        $content = isset($_POST['ads_txt_content']) ? wp_unslash($_POST['ads_txt_content']) : '';
        $content = sanitize_textarea_field($content);
        
        if (preg_match('/<[^>]*>/', $content) || 
            stripos($content, 'javascript:') !== false ||
            stripos($content, 'data:') !== false) {
            $save_error = true;
        }
        elseif (strlen($content) > 1024 * 1024) {
            $save_error = true;
        } else {
            $ads_txt_path = ABSPATH . 'ads.txt';
            
            if ($wp_filesystem->put_contents($ads_txt_path, $content, FS_CHMOD_FILE)) {
                $settings_updated = true;
            } else {
                $save_error = true;
            }
        }
        
        $robots_content = isset($_POST['robots_txt_content']) ? wp_unslash($_POST['robots_txt_content']) : '';
        $robots_content = sanitize_textarea_field($robots_content);
        
        if (preg_match('/<[^>]*>/', $robots_content) || 
            stripos($robots_content, 'javascript:') !== false ||
            stripos($robots_content, 'data:') !== false) {
            $robots_save_error = true;
        }
        elseif (strlen($robots_content) > 1024 * 1024) {
            $robots_save_error = true;
        } else {
            $robots_txt_path = ABSPATH . 'robots.txt';
            
            if ($wp_filesystem->put_contents($robots_txt_path, $robots_content, FS_CHMOD_FILE)) {
                $robots_settings_updated = true;
            } else {
                $robots_save_error = true;
            }
        }
    } else {
        $save_error = true;
        $robots_save_error = true;
    }
}

if (isset($_POST['upload_naver_verification'])) {
    check_admin_referer('presslearn_save_ads_txt_nonce');
    
    if (current_user_can('manage_options') && $filesystem_initialized) {
        if (isset($_FILES['naver_verification_file']) && $_FILES['naver_verification_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['naver_verification_file'];
            $filename = sanitize_file_name($file['name']);
            
            if (preg_match('/^naver[a-z0-9]+\.html$/i', $filename) && $file['size'] <= 1024 * 1024) {
                global $wp_filesystem;
                $target_path = ABSPATH . $filename;
                
                $file_content = file_get_contents($file['tmp_name']);
                if ($file_content !== false && $wp_filesystem->put_contents($target_path, $file_content, FS_CHMOD_FILE)) {
                    $naver_upload_success = true;
                } else {
                    $upload_error = true;
                }
            } else {
                $upload_error = true;
            }
        } else {
            $upload_error = true;
        }
    } else {
        $upload_error = true;
    }
}

if (isset($_POST['upload_google_verification'])) {
    check_admin_referer('presslearn_save_ads_txt_nonce');
    
    if (current_user_can('manage_options') && $filesystem_initialized) {
        if (isset($_FILES['google_verification_file']) && $_FILES['google_verification_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['google_verification_file'];
            $filename = sanitize_file_name($file['name']);
            
            if (preg_match('/^google[a-z0-9]+\.html$/i', $filename) && $file['size'] <= 1024 * 1024) {
                global $wp_filesystem;
                $target_path = ABSPATH . $filename;
                
                $file_content = file_get_contents($file['tmp_name']);
                if ($file_content !== false && $wp_filesystem->put_contents($target_path, $file_content, FS_CHMOD_FILE)) {
                    $google_upload_success = true;
                } else {
                    $upload_error = true;
                }
            } else {
                $upload_error = true;
            }
        } else {
            $upload_error = true;
        }
    } else {
        $upload_error = true;
    }
}

if ($filesystem_initialized) {
    global $wp_filesystem;
    $ads_txt_path = ABSPATH . 'ads.txt';
    
    if ($wp_filesystem->exists($ads_txt_path)) {
        $existing_content = $wp_filesystem->get_contents($ads_txt_path);
        if ($existing_content === false) {
            $existing_content = '';
        } else {
            if (strlen($existing_content) > 1024 * 1024) {
                $existing_content = '';
            }
        }
    }
} else {
    $existing_content = '';
}

if ($filesystem_initialized) {
    global $wp_filesystem;
    $robots_txt_path = ABSPATH . 'robots.txt';
    
    if ($wp_filesystem->exists($robots_txt_path)) {
        $robots_existing_content = $wp_filesystem->get_contents($robots_txt_path);
        if ($robots_existing_content === false) {
            $robots_existing_content = '';
        } else {
            if (strlen($robots_existing_content) > 1024 * 1024) {
                $robots_existing_content = '';
            }
        }
    }
} else {
    $robots_existing_content = '';
}

$naver_verification_exists = false;
$google_verification_exists = false;
$naver_verification_file = '';
$google_verification_file = '';

if ($filesystem_initialized) {
    global $wp_filesystem;
    
    $files = $wp_filesystem->dirlist(ABSPATH);
    if ($files) {
        foreach ($files as $file) {
            if (preg_match('/^naver[a-z0-9]+\.html$/i', $file['name'])) {
                $naver_verification_exists = true;
                $naver_verification_file = $file['name'];
                break;
            }
        }
        
        foreach ($files as $file) {
            if (preg_match('/^google[a-z0-9]+\.html$/i', $file['name'])) {
                $google_verification_exists = true;
                $google_verification_file = $file['name'];
                break;
            }
        }
    }
}

$ads_txt_url = home_url('/ads.txt');
$file_exists = !empty($existing_content);
$robots_txt_url = home_url('/robots.txt');
$robots_file_exists = !empty($robots_existing_content);
?>

<div class="presslearn-header">
    <div class="presslearn-header-logo">
        <img src="<?php echo esc_url(PRESSLEARN_PLUGIN_URL); ?>assets/images/logo.png" alt="PressLearn Logo">
        <h1>Ads 매니저</h1>
    </div>
    <div class="presslearn-header-status">
        <div class="presslearn-header-status-item status-activate">
            <p>기능이 활성화 되었습니다.</p>
        </div>
    </div>
</div>

<div class="wrap">
    <div class="presslearn-breadcrumbs-wrap">
        <div class="presslearn-breadcrumbs">
            <span>대시보드</span>
            <span class="divider">/</span>
            <span class="active">Ads 매니저</span>
        </div>
    </div>
    
    <?php if (isset($filesystem_error)): ?>
        <div class="notice notice-error inline">
            <p>파일 시스템에 접근할 수 없습니다. 서버 권한을 확인해주세요.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($settings_updated || $robots_settings_updated): ?>
        <div class="notice notice-success inline">
            <p>파일이 성공적으로 저장되었습니다.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($save_error || $robots_save_error): ?>
        <div class="notice notice-error inline">
            <p>파일 저장에 실패했습니다. 파일 권한을 확인해주세요.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($naver_upload_success): ?>
        <div class="notice notice-success inline">
            <p>네이버 서치 어드바이저 인증 파일이 성공적으로 업로드되었습니다</p>
        </div>
    <?php endif; ?>
    
    <?php if ($google_upload_success): ?>
        <div class="notice notice-success inline">
            <p>구글 서치 콘솔 인증 파일이 성공적으로 업로드되었습니다.</p>
        </div>
    <?php endif; ?>
    
    <?php if ($upload_error): ?>
        <div class="notice notice-error inline">
            <p>파일 업로드에 실패했습니다. 파일 형식과 크기를 확인해주세요.</p>
        </div>
    <?php endif; ?>

    <div class="presslearn-card">
        <div class="presslearn-card-header">
            <h2>Ads 매니저</h2>
            <p>Google AdSense 및 기타 광고 네트워크 인증을 위한 ads.txt 파일을 관리합니다.</p>
        </div>
        <div class="presslearn-card-body">
            <form method="post" action="" enctype="multipart/form-data" id="ads-manager-form">
                <?php wp_nonce_field('presslearn_save_ads_txt_nonce'); ?>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>현재 파일 상태</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <div style="margin-bottom: 8px;">
                            <?php if ($file_exists): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450; margin-right: 8px;"></span>
                                <strong>ads.txt 파일이 존재합니다</strong>
                            <?php else: ?>
                                <span class="dashicons dashicons-marker" style="color: #dc3232; margin-right: 8px;"></span>
                                <strong>ads.txt 파일이 존재하지 않습니다</strong>
                            <?php endif; ?>
                        </div>
                        <div>
                            <?php if ($robots_file_exists): ?>
                                <span class="dashicons dashicons-yes-alt" style="color: #46b450; margin-right: 8px;"></span>
                                <strong>robots.txt 파일이 존재합니다</strong>
                            <?php else: ?>
                                <span class="dashicons dashicons-marker" style="color: #dc3232; margin-right: 8px;"></span>
                                <strong>robots.txt 파일이 존재하지 않습니다</strong>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>ads.txt 파일 내용</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <textarea 
                            name="ads_txt_content" 
                            id="ads_txt_content" 
                            rows="10" 
                            class="large-text code"
                            style="width: 100%; font-family: Consolas, Monaco, monospace; font-size: 13px;"
                            placeholder="google.com, pub-0000000000000000, DIRECT, f08c47fec0942fa0&#10;example.com, 12345, DIRECT"
                            <?php echo !$filesystem_initialized ? 'disabled' : ''; ?>
                        ><?php echo esc_textarea($existing_content); ?></textarea>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>robots.txt 파일 내용</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <textarea 
                            name="robots_txt_content" 
                            id="robots_txt_content" 
                            rows="10" 
                            class="large-text code"
                            style="width: 100%; font-family: Consolas, Monaco, monospace; font-size: 13px;"
                            placeholder="User-agent: *&#10;Disallow: /wp-admin/&#10;Allow: /wp-admin/admin-ajax.php"
                            <?php echo !$filesystem_initialized ? 'disabled' : ''; ?>
                        ><?php echo esc_textarea($robots_existing_content); ?></textarea>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>네이버 서치 어드바이저 인증 업로드</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <?php if (!$naver_verification_exists && $filesystem_initialized): ?>
                            <input type="file" name="naver_verification_file" accept=".html" style="margin-bottom: 10px; width: 100%;">
                            <button type="submit" name="upload_naver_verification" value="1" class="point-btn">파일 업로드</button>
                        <?php else: ?>
                            <button type="button" class="point-btn" disabled style="opacity: 0.6;">
                                <?php echo $naver_verification_exists ? '업로드됨' : '사용 불가'; ?>
                            </button>
                        <?php endif; ?>
                        <?php if ($naver_verification_exists): ?>
                            <p style="color: #46b450; font-size: 13px; margin-top: 5px;">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php echo esc_html($naver_verification_file); ?> 업로드됨
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="presslearn-card-body-row">
                    <div class="presslearn-card-body-row-item grid-left">
                        <h3>구글 서치 콘솔 인증 업로드</h3>
                    </div>
                    <div class="presslearn-card-body-row-item grid-right">
                        <?php if (!$google_verification_exists && $filesystem_initialized): ?>
                            <input type="file" name="google_verification_file" accept=".html" style="margin-bottom: 10px; width: 100%;">
                            <button type="submit" name="upload_google_verification" value="1" class="point-btn">파일 업로드</button>
                        <?php else: ?>
                            <button type="button" class="point-btn" disabled style="opacity: 0.6;">
                                <?php echo $google_verification_exists ? '업로드됨' : '사용 불가'; ?>
                            </button>
                        <?php endif; ?>
                        <?php if ($google_verification_exists): ?>
                            <p style="color: #46b450; font-size: 13px; margin-top: 5px;">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php echo esc_html($google_verification_file); ?> 업로드됨
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
        <div class="presslearn-card-footer">
            <div class="presslearn-card-footer-item grid-left">
                <span class="tip_button">robots.txt 파일은 Rank Math SEO 플러그인이 있어도, 우선 순위입니다.</span>
            </div>
            <div class="presslearn-card-footer-item grid-right">
                <?php if ($filesystem_initialized): ?>
                    <button type="submit" name="save_ads_txt_content" value="1" class="point-btn" form="ads-manager-form">저장하기</button>
                <?php else: ?>
                    <button type="button" class="point-btn" disabled style="opacity: 0.6;">저장하기</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> 