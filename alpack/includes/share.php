<?php
if (!defined('ABSPATH')) {
    exit;
}

function presslearn_is_plugin_active_for_social_share() {
    return get_option('presslearn_social_share_enabled', 'no') === 'yes';
}

/**
 * Social Share admin styles
 */
function presslearn_social_share_admin_styles() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-social-share') === false) {
        return;
    }
    
    wp_register_style(
        'presslearn-social-share-admin-css',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION
    );
    wp_enqueue_style('presslearn-social-share-admin-css');
    
    $social_share_admin_css = "
    .social-share-options {
        display: flex;
        flex-wrap: wrap;
    }
    
    .social-share-button {
        display: inline-block;
        margin: 0;
    }
    
    .social-share-button input[type='checkbox'] {
        display: none;
    }
    
    .social-btn {
        display: inline-block;
        padding: 8px 16px;
        background-color: #f8f9fa;
        border: 1px solid #b5bfc9;
        border-radius: 5px;
        color: #333;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .social-share-button input[type='checkbox']:checked + .social-btn {
        background-color: #2196F3;
        color: white;
        border-color: #2196F3;
    }
    
    .social-share-button input[type='checkbox']:disabled + .social-btn {
        opacity: 0.3;
        cursor: not-allowed;
    }
    
    #social-share-shortcode {
        resize: none;
        font-family: monospace;
        background-color: #f9f9f9;
    }
    ";
    
    wp_add_inline_style('presslearn-social-share-admin-css', $social_share_admin_css);
}

/**
 * Social Share admin scripts
 */
function presslearn_social_share_admin_scripts() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-social-share') === false) {
        return;
    }
    
    wp_register_script(
        'presslearn-social-share-kakao-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-social-share-kakao-js');
    
    $kakao_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const kakaoApiKeyInput = document.getElementById('kakao-api-key');
        const kakaoCheckbox = document.getElementById('kakaotalk-option');
        const kakaoLabel = document.getElementById('kakaotalk-option-label');
        
        if (kakaoApiKeyInput && kakaoCheckbox && kakaoLabel) {
            function updateKakaoOption() {
                const apiKey = kakaoApiKeyInput.value.trim();
                
                if (apiKey === '') {
                    kakaoCheckbox.disabled = true;
                    kakaoCheckbox.checked = false;
                    kakaoLabel.style.opacity = '0.3';
                    kakaoLabel.style.cursor = 'not-allowed';
                } else {
                    kakaoCheckbox.disabled = false;
                    kakaoLabel.style.opacity = '1';
                    kakaoLabel.style.cursor = 'pointer';
                }
            }
            
            updateKakaoOption();
            
            kakaoApiKeyInput.addEventListener('input', updateKakaoOption);
        }
    });
    ";
    
    wp_add_inline_script('presslearn-social-share-kakao-js', $kakao_js);
    
    wp_register_script(
        'presslearn-social-share-style-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-social-share-style-js');
    
    $style_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const styleSelect = document.getElementById('social-share-style');
        const alignmentSelect = document.getElementById('social-share-alignment');
        
        if (styleSelect && alignmentSelect) {
            function toggleFullWidthOption() {
                const style = styleSelect.value;
                const fullOption = alignmentSelect.querySelector('option[value=\"full\"]');
                
                if (style === 'default') {
                    fullOption.disabled = true;
                    fullOption.style.display = 'none';
                    
                    if (alignmentSelect.value === 'full') {
                        alignmentSelect.value = 'left';
                    }
                } else {
                    fullOption.disabled = false;
                    fullOption.style.display = 'block';
                }
            }
            
            toggleFullWidthOption();
            styleSelect.addEventListener('change', toggleFullWidthOption);
        }
    });
    ";
    
    wp_add_inline_script('presslearn-social-share-style-js', $style_js);
    
    wp_register_script(
        'presslearn-social-share-clipboard-js',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-social-share-clipboard-js');
    
    $clipboard_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const copyButton = document.getElementById('copy-social-share-shortcode');
        if (copyButton) {
            copyButton.addEventListener('click', function() {
                copyToClipboard('#social-share-shortcode');
            });
        }
    });
    
    function copyToClipboard(element) {
        const targetElement = document.querySelector(element);
        if (targetElement) {
            const tempInput = document.createElement('input');
            document.body.appendChild(tempInput);
            tempInput.value = targetElement.value;
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('숏코드가 클립보드에 복사되었습니다!');
        }
    }
    ";
    
    wp_add_inline_script('presslearn-social-share-clipboard-js', $clipboard_js);
}

/**
 * Frontend Social Share styles and scripts
 */
function presslearn_social_share_frontend_assets() {
    if (is_admin() || !presslearn_is_plugin_active_for_social_share()) {
        return;
    }
    
    wp_register_style('presslearn-social-share-style', false);
    wp_enqueue_style('presslearn-social-share-style');
    
    $custom_css = "
    .presslearn-social-share {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 30px 0;
    }
    
    .presslearn-social-share.full-width {
        justify-content: space-between;
        width: 100%;
    }
    
    .presslearn-social-share.full-width .presslearn-social-share-button {
        flex: 1;
        width: 100%;
        text-align: center;
        justify-content: center;
    }
    
    .presslearn-social-share.inline {
        display: inline-flex;
        margin: 0 10px;
        vertical-align: middle;
    }
    
    .presslearn-social-share-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        background-color: #f8f9fa;
        border: 1px solid #b5bfc9;
        border-radius: 10px;
        color: #333;
        font-size: 16px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        gap: 6px;
    }
    
    .presslearn-social-share-button:hover {
        background-color: #e9e9e9;
        color: #FFF;
    }
    
    .presslearn-facebook {
        background-color: #1877f2;
        color: white !important;
        border-color: #1877f2;
    }
    
    .presslearn-facebook:hover {
        background-color: #166fe5;
        border-color: #166fe5;
    }
    
    .presslearn-twitter {
        background-color: #000000;
        color: white !important;
        border-color: #000000;
    }
    
    .presslearn-twitter:hover {
        background-color: #333333;
        border-color: #333333;
    }
    
    .presslearn-kakaotalk {
        background-color: #fee500;
        color: #000 !important;
        border-color: #fee500;
    }
    
    .presslearn-kakaotalk:hover {
        background-color: #f4dc00;
        border-color: #f4dc00;
    }

    .presslearn-kakaotalk:hover span {
        color: #000 !important;
    }
    
    .presslearn-naver {
        background-color: #03c75a;
        color: white !important;
        border-color: #03c75a;
    }
    
    .presslearn-naver:hover {
        background-color: #02b350;
        border-color: #02b350;
    }
    
    .presslearn-band {
        background-color: #00c73c;
        color: white !important;
        border-color: #00c73c;
    }
    
    .presslearn-band:hover {
        background-color: #00b636;
        border-color: #00b636;
    }
    
    .presslearn-line {
        background-color: #06c755;
        color: white !important;
        border-color: #06c755;
    }
    
    .presslearn-line:hover {
        background-color: #05b64c;
        border-color: #05b64c;
    }
    
    .presslearn-social-share-icon {
        width: 16px;
        height: 16px;
        display: inline-block;
    }

    .presslearn-social-share-icon img {
        width: 100%;
    }
    
    .presslearn-share-popup-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        background-color: #f8f9fa;
        border: 1px solid #b5bfc9;
        border-radius: 10px;
        color: #333;
        font-size: 16px;
        font-weight: 500;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
        gap: 10px;
    }
    
    .presslearn-share-popup-trigger:hover {
        background-color: #e9e9e9;
    }
    
    .presslearn-share-popup {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    
    .presslearn-share-popup-content {
        background-color: #fff;
        border-radius: 10px;
        padding: 20px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .presslearn-share-popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }
    
    .presslearn-share-popup-title {
        font-size: 16px;
        font-weight: bold;
        margin: 0;
    }
    
    .presslearn-share-popup-close {
        background: none !important;
        border: none !important;
        font-size: 20px !important;
        cursor: pointer !important;
        padding: 0 !important;
        color: #666 !important;
    }
    
    .presslearn-share-popup-buttons {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    
    .presslearn-share-popup .presslearn-social-share-button {
        display: flex;
        flex-direction: column;
        height: auto;
        padding: 10px;
        border-radius: 8px;
        text-align: center;
    }
    
    .presslearn-share-popup .presslearn-social-share-icon {
        width: 24px;
        height: 24px;
    }
    .full-width a {
        width: 100%;
    }

    @media screen and (max-width: 782px) {
        .presslearn-share-popup-content {
            width: 100%;
            max-width: 90%;
        }
    }

    a.presslearn-social-share-button {
        text-decoration: none !important;
    }
    ";
    
    wp_add_inline_style('presslearn-social-share-style', $custom_css);
    
    wp_register_script('presslearn-social-share-script', false, array(), PRESSLEARN_PLUGIN_VERSION, true);
    wp_enqueue_script('presslearn-social-share-script');
    
    $script = "
    document.addEventListener('DOMContentLoaded', function() {
        const shareButtons = document.querySelectorAll('.presslearn-social-share-button');
        
        shareButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const social = this.getAttribute('data-social');
                const url = this.getAttribute('data-url');
                const title = this.getAttribute('data-title');
            
            if (social === 'copy') {
                e.preventDefault();
                navigator.clipboard.writeText(url).then(function() {
                    alert('링크가 복사되었습니다.');
                }).catch(function(err) {
                    console.error('복사 실패:', err);
                });
                return;
            }
            
                if (social === 'kakaotalk') {
                    e.preventDefault();
                    return;
                }
                
                const width = 570;
                const height = 450;
                const left = (screen.width - width) / 2;
                const top = (screen.height - height) / 2;
                const options = 'width=' + width + ',height=' + height + ',top=' + top + ',left=' + left;
            
                window.open(this.getAttribute('href'), social, options);
            
            e.preventDefault();
            });
        });
        
        const popupTriggers = document.querySelectorAll('.presslearn-share-popup-trigger');
        popupTriggers.forEach(trigger => {
            trigger.addEventListener('click', function(e) {
            e.preventDefault();
                const popup = document.querySelector('.presslearn-share-popup');
                if (popup) {
                    popup.style.display = 'flex';
                    popup.style.opacity = '0';
                    popup.style.transition = 'opacity 0.2s';
                    setTimeout(() => {
                        popup.style.opacity = '1';
                    }, 10);
                }
            });
        });
        
        const closeButtons = document.querySelectorAll('.presslearn-share-popup-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const popup = document.querySelector('.presslearn-share-popup');
                if (popup) {
                    popup.style.opacity = '0';
                    setTimeout(() => {
                        popup.style.display = 'none';
                    }, 200);
                }
            });
        });
        
        document.addEventListener('click', function(e) {
            const popup = document.querySelector('.presslearn-share-popup');
            if (popup && popup.style.display === 'flex') {
                if (!e.target.closest('.presslearn-share-popup-content') && 
                    !e.target.closest('.presslearn-share-popup-trigger')) {
                    popup.style.opacity = '0';
                    setTimeout(() => {
                        popup.style.display = 'none';
                    }, 200);
                }
            }
        });
    });
    ";
    
    wp_add_inline_script('presslearn-social-share-script', $script);
}

function presslearn_init_social_share() {
    $social_share_enabled = get_option('presslearn_social_share_enabled', 'no');
    
    if ($social_share_enabled !== 'yes') {
        return;
    }
    
    add_shortcode('presslearn_social_share', 'presslearn_social_share_shortcode');
    
    add_action('wp_enqueue_scripts', 'presslearn_social_share_frontend_assets');
}
add_action('init', 'presslearn_init_social_share');

function presslearn_social_share_shortcode($atts) {
    $social_share_enabled = get_option('presslearn_social_share_enabled', 'no');
    
    if ($social_share_enabled !== 'yes') {
        return '';
    }
    
    $atts = shortcode_atts(array(
        'title' => '',
    ), $atts, 'presslearn_social_share');
    
    $title = !empty($atts['title']) ? $atts['title'] : get_the_title();
    $url = get_permalink();
    $align = get_option('presslearn_social_share_alignment', 'left');
    
    $social_share_options = get_option('presslearn_social_share_options', array('facebook', 'twitter', 'kakaotalk', 'naver', 'band', 'line'));
    $social_share_style = get_option('presslearn_social_share_style', 'default');
    
    if (empty($social_share_options)) {
        return '';
    }
    
    $alignment_data = presslearn_get_share_alignment($align, $social_share_style);
    $alignment_style = $alignment_data['style'];
    $alignment_class = $alignment_data['class'];
    
    if ($social_share_style === 'popup') {
        $html = '<div class="presslearn-social-share ' . esc_attr($alignment_class) . '" style="' . esc_attr($alignment_style) . '">';
        $html .= '<a href="javascript:void(0)" class="presslearn-share-popup-trigger">
            <span class="presslearn-social-share-icon">
                <img src="' . esc_url(plugins_url('/assets/images/share.png', dirname(__FILE__))) . '" alt="Share" width="16" height="16">
            </span>
            <span>공유하기</span>
        </a>';
        
        $html .= '<div class="presslearn-share-popup">
            <div class="presslearn-share-popup-content">
                <div class="presslearn-share-popup-header">
                    <h4 class="presslearn-share-popup-title">공유하기</h4>
                    <button type="button" class="presslearn-share-popup-close">&times;</button>
                </div>
                <div class="presslearn-share-popup-buttons">';
        
        foreach ($social_share_options as $social) {
            switch ($social) {
                case 'facebook':
                    $html .= presslearn_facebook_share_button($url, $title);
                    break;
                    
                case 'twitter':
                    $html .= presslearn_twitter_share_button($url, $title);
                    break;
                    
                case 'kakaotalk':
                    $html .= presslearn_kakaotalk_share_button($url, $title);
                    break;
                    
                case 'naver':
                    $html .= presslearn_naver_share_button($url, $title);
                    break;
                    
                case 'band':
                    $html .= presslearn_band_share_button($url, $title);
                    break;
                    
                case 'line':
                    $html .= presslearn_line_share_button($url, $title);
                    break;
            }
        }
        
        $html .= '<a href="javascript:void(0)" class="presslearn-social-share-button" data-social="copy" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '">
            <span class="presslearn-social-share-icon">
                <img src="' . esc_url(plugins_url('/assets/images/copy.png', dirname(__FILE__))) . '" alt="Copy link" width="16" height="16">
            </span>
            <span style="color: #000 !important;">링크 복사</span>
        </a>';
        
        $html .= '</div></div></div></div>';
        
        return $html;
    }
    
    $html = '<div class="presslearn-social-share ' . esc_attr($alignment_class) . '" style="' . esc_attr($alignment_style) . '">';
    
    foreach ($social_share_options as $social) {
        switch ($social) {
            case 'facebook':
                $html .= presslearn_facebook_share_button($url, $title);
                break;
                
            case 'twitter':
                $html .= presslearn_twitter_share_button($url, $title);
                break;
                
            case 'kakaotalk':
                $html .= presslearn_kakaotalk_share_button($url, $title);
                break;
                
            case 'naver':
                $html .= presslearn_naver_share_button($url, $title);
                break;
                
            case 'band':
                $html .= presslearn_band_share_button($url, $title);
                break;
                
            case 'line':
                $html .= presslearn_line_share_button($url, $title);
                break;
        }
    }
    
    $html .= '<a href="javascript:void(0)" class="presslearn-social-share-button" data-social="copy" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url(plugins_url('/assets/images/copy.png', dirname(__FILE__))) . '" alt="Copy link" width="16" height="16">
        </span>
        <span style="color: #000 !important;">링크 복사</span>
    </a>';
    
    $html .= '</div>';
    
    return $html;
}

function presslearn_get_share_alignment($align, $style = 'default') {
    $style_attr = '';
    $class = '';
    
    if ($align === 'full' && $style !== 'popup') {
        $align = 'left';
    }
    
    switch ($align) {
        case 'center':
            $style_attr = 'justify-content: center;';
            break;
        case 'right':
            $style_attr = 'justify-content: flex-end;';
            break;
        case 'full':
            $class = 'full-width';
            break;
        case 'inline':
            $class = 'inline';
            break;
        case 'left':
        default:
            $style_attr = 'justify-content: flex-start;';
            break;
    }
    
    return array(
        'style' => $style_attr,
        'class' => $class
    );
}

function presslearn_facebook_share_button($url, $title) {
    $share_url = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($url);
    $icon_url = plugins_url('/assets/images/facebook.png', dirname(__FILE__));
    
    return '<a href="' . esc_url($share_url) . '" class="presslearn-social-share-button presslearn-facebook" data-social="facebook" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '" target="_blank" rel="noopener noreferrer">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url($icon_url) . '" alt="Facebook" width="16" height="16">
        </span>
        <span>페이스북</span>
    </a>';
}

function presslearn_twitter_share_button($url, $title) {
    $share_url = 'https://twitter.com/intent/tweet?url=' . urlencode($url) . '&text=' . urlencode($title);
    $icon_url = plugins_url('/assets/images/twitter.png', dirname(__FILE__));
    
    return '<a href="' . esc_url($share_url) . '" class="presslearn-social-share-button presslearn-twitter" data-social="twitter" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '" target="_blank" rel="noopener noreferrer">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url($icon_url) . '" alt="Twitter" width="16" height="16">
        </span>
        <span>X</span>
    </a>';
}

function presslearn_kakaotalk_share_button($url, $title) {
    if (!wp_script_is('kakao-sdk', 'registered')) {
        wp_register_script('kakao-sdk', PRESSLEARN_PLUGIN_URL . 'assets/js/kakao.min.js', array(), null, true);
        wp_enqueue_script('kakao-sdk');
        
        $kakao_api_key = get_option('presslearn_kakao_api_key', '');
        wp_add_inline_script('kakao-sdk', "
            if (typeof Kakao !== 'undefined') {
                Kakao.init('" . esc_js($kakao_api_key) . "');
            }
        ");
    }
    
    $thumbnail = '';
    if (is_single() || is_page()) {
        $post_id = get_the_ID();
        if (has_post_thumbnail($post_id)) {
            $thumbnail = get_the_post_thumbnail_url($post_id, 'large');
        }
    }
    
    $description = '';
    if (is_single() || is_page()) {
        $post = get_post();
        if ($post) {
            $excerpt = $post->post_excerpt;
            if (empty($excerpt)) {
                $excerpt = wp_strip_all_tags(get_the_content());
            }
            $description = wp_html_excerpt($excerpt, 100, '...');
        }
    }
    
    wp_add_inline_script('presslearn-social-share-script', "
        document.addEventListener('DOMContentLoaded', function() {
            const kakaoButtons = document.querySelectorAll('.presslearn-kakaotalk');
            kakaoButtons.forEach(button => {
                button.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (typeof Kakao === 'undefined') {
                alert('카카오톡 공유 기능을 사용할 수 없습니다.');
                return;
            }
            
                    const shareUrl = this.getAttribute('data-url');
                    const shareTitle = this.getAttribute('data-title');
                    const shareImage = '" . esc_js($thumbnail) . "';
                    const shareDesc = '" . esc_js($description) . "';
            
            Kakao.Share.sendDefault({
                objectType: 'feed',
                content: {
                    title: shareTitle,
                    description: shareDesc,
                    imageUrl: shareImage || '',
                    link: {
                        mobileWebUrl: shareUrl,
                        webUrl: shareUrl
                    }
                },
                buttons: [
                    {
                        title: '웹으로 보기',
                        link: {
                            mobileWebUrl: shareUrl,
                            webUrl: shareUrl
                        }
                    }
                ]
                    });
                });
            });
        });
    ");
    
    $icon_url = plugins_url('/assets/images/kakao.png', dirname(__FILE__));
    
    return '<a href="javascript:void(0)" class="presslearn-social-share-button presslearn-kakaotalk" data-social="kakaotalk" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url($icon_url) . '" alt="KakaoTalk" width="16" height="16">
        </span>
        <span>카카오톡</span>
    </a>';
}

function presslearn_naver_share_button($url, $title) {
    $share_url = 'https://share.naver.com/web/shareView?url=' . urlencode($url) . '&title=' . urlencode($title);
    $icon_url = plugins_url('/assets/images/naver.png', dirname(__FILE__));
    
    return '<a href="' . esc_url($share_url) . '" class="presslearn-social-share-button presslearn-naver" data-social="naver" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '" target="_blank" rel="noopener noreferrer">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url($icon_url) . '" alt="Naver" width="16" height="16">
        </span>
        <span>네이버</span>
    </a>';
}

function presslearn_band_share_button($url, $title) {
    $share_url = 'https://band.us/plugin/share?body=' . urlencode($title) . '%0A' . urlencode($url) . '&route=' . urlencode($url);
    $icon_url = plugins_url('/assets/images/band.png', dirname(__FILE__));
    
    return '<a href="' . esc_url($share_url) . '" class="presslearn-social-share-button presslearn-band" data-social="band" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '" target="_blank" rel="noopener noreferrer">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url($icon_url) . '" alt="Band" width="16" height="16">
        </span>
        <span>밴드</span>
    </a>';
}

function presslearn_line_share_button($url, $title) {
    $share_url = 'https://social-plugins.line.me/lineit/share?url=' . urlencode($url);
    $icon_url = plugins_url('/assets/images/line.png', dirname(__FILE__));
    
    return '<a href="' . esc_url($share_url) . '" class="presslearn-social-share-button presslearn-line" data-social="line" data-url="' . esc_url($url) . '" data-title="' . esc_attr($title) . '" target="_blank" rel="noopener noreferrer">
        <span class="presslearn-social-share-icon">
            <img src="' . esc_url($icon_url) . '" alt="Line" width="16" height="16">
        </span>
        <span>라인</span>
    </a>';
}

function presslearn_get_social_share_shortcode() {
    return '[presslearn_social_share]';
} 

add_action('admin_enqueue_scripts', 'presslearn_social_share_admin_styles');
add_action('admin_enqueue_scripts', 'presslearn_social_share_admin_scripts'); 