<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin functionality for PressLearn Plugin
 */

/**
 * Admin settings styles
 */
function presslearn_admin_settings_styles() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-settings') === false) {
        return;
    }
    
    wp_register_style(
        'presslearn-admin-settings-css',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION
    );
    wp_enqueue_style('presslearn-admin-settings-css');
    
    $admin_settings_css = "
    /* Admin Settings specific styles can be added here if needed */
    ";
    
    wp_add_inline_style('presslearn-admin-settings-css', $admin_settings_css);
}

/**
 * Admin settings scripts
 */
function presslearn_admin_settings_scripts() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-settings') === false) {
        return;
    }
    
    wp_register_script(
        'presslearn-admin-login-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-admin-login-js');
    
    wp_localize_script('presslearn-admin-login-js', 'presslearn_admin_login', array(
        'status_url' => rest_url('presslearn/v1/status'),
        'banner_url' => rest_url('presslearn/v1/banner')
    ));
    
    $login_js = "
    document.addEventListener('DOMContentLoaded', function() {
        let loginWindow = null;
        
        function checkAPIStatus() {
            fetch(presslearn_admin_login.status_url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.is_active) {
                    if (loginWindow && !loginWindow.closed) {
                        loginWindow.close();
                    }
                    location.reload();
                }
            })
            .catch(error => {
                console.log('Status check failed:', error);
            });
        }

        const loginButton = document.getElementById('presslearn-login-button');
        if (loginButton) {
            loginButton.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (loginWindow && !loginWindow.closed) {
                    loginWindow.focus();
                    return;
                }
                
                const loginUrl = this.getAttribute('href');
                loginWindow = window.open(loginUrl, 'presslearn_login', 'width=600,height=700');
                
                if (loginWindow) {
                    const checkInterval = setInterval(function() {
                        checkAPIStatus();
                    }, 1000);
                    
                    const windowCheckInterval = setInterval(function() {
                        if (loginWindow.closed) {
                            clearInterval(checkInterval);
                            clearInterval(windowCheckInterval);
                            setTimeout(checkAPIStatus, 1000);
                        }
                    }, 1000);
                    
                    setTimeout(function() {
                        clearInterval(checkInterval);
                        clearInterval(windowCheckInterval);
                    }, 30000);
                } else {
                    alert('팝업 창이 차단되었습니다. 팝업 차단을 해제한 후 다시 시도해주세요.');
                }
            });
        }
    });
    ";
    
    wp_add_inline_script('presslearn-admin-login-js', $login_js);
    
    wp_register_script(
        'presslearn-admin-banner-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-admin-banner-js');
    
    wp_localize_script('presslearn-admin-banner-js', 'presslearn_admin_banner', array(
        'banner_url' => rest_url('presslearn/v1/banner')
    ));
    
    $banner_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const bannerContainer = document.getElementById('presslearn-banner-container');
        const banner = document.getElementById('presslearn-banner');
        const bannerSkeleton = document.getElementById('presslearn-banner-skeleton');
        const bannerLink = document.getElementById('presslearn-banner-link');
        const bannerImage = document.getElementById('presslearn-banner-image');
        
        function fetchBanner() {
            if (!bannerContainer || !banner || !bannerSkeleton || !bannerLink || !bannerImage) {
                return;
            }
            
            banner.style.display = 'none';
            bannerSkeleton.style.display = 'block';
            
            fetch(presslearn_admin_banner.banner_url, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    bannerImage.src = data.data.url;
                    bannerLink.href = data.data.href_url;
                    bannerImage.alt = data.data.title || 'PressLearn Banner';
                    
                    banner.style.display = 'block';
                    bannerSkeleton.style.display = 'none';
                } else {
                    bannerSkeleton.style.display = 'none';
                }
            })
            .catch(error => {
                console.log('Banner load failed:', error);
                bannerSkeleton.style.display = 'none';
            });
        }
        
        if (bannerContainer && banner && bannerSkeleton && bannerLink && bannerImage) {
            fetchBanner();
            setInterval(fetchBanner, 300000);
        }
    });
    ";
    
    wp_add_inline_script('presslearn-admin-banner-js', $banner_js);
}

add_action('admin_enqueue_scripts', 'presslearn_admin_settings_styles');
add_action('admin_enqueue_scripts', 'presslearn_admin_settings_scripts');

