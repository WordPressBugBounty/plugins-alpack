/**
 * PressLearn Plugin Admin JavaScript
 */

(function($) {
    'use strict';

    var PressLearnPlugin = {
        init: function() {
            this.cacheDom();
            this.bindEvents();
            this.checkLoginStatus();
        },
        
        cacheDom: function() {
            this.$statusSection = $('.presslearn-status-section');
            this.$loginButton = $('.kakao-login-button');
            this.$statusMessage = $('.status-message');
        },
        
        bindEvents: function() {
            this.$loginButton.on('click', this.openLoginPopup.bind(this));
            
            $(window).on('message', this.handleLoginMessage.bind(this));
        },
        
        checkLoginStatus: function() {
            if (presslearn_admin.is_default_permalink) {
                return;
            }
            
            $.ajax({
                url: presslearn_admin.api_url + '/status',
                method: 'GET',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', presslearn_admin.nonce);
                },
                success: function(response) {
                    if (response.is_active) {
                        PressLearnPlugin.activatePluginUI();
                    }
                }
            });
        },
        
        openLoginPopup: function(e) {
            e.preventDefault();
            
            if (this.$loginButton.is(':disabled')) {
                alert('퍼머링크 설정을 먼저 변경해주세요.\n\n설정 → 고유주소에서 "기본형" 대신 "포스트명" 또는 다른 옵션을 선택하세요.');
                return false;
            }
            
            var url = this.$loginButton.attr('href');
            var popupWidth = 500;
            var popupHeight = 600;
            var left = (screen.width / 2) - (popupWidth / 2);
            var top = (screen.height / 2) - (popupHeight / 2);
            
            window.open(
                url, 
                'presslearn_login',
                'width=' + popupWidth + ',height=' + popupHeight + ',top=' + top + ',left=' + left
            );
            
            return false;
        },
        
        handleLoginMessage: function(event) {
            if (event.originalEvent.origin.indexOf('presslearn.co.kr') === -1) {
                return;
            }
            
            var data = event.originalEvent.data;
            
            if (data && data.key) {
                this.activatePlugin(data.key);
            }
        },
        
        activatePlugin: function(key) {
            $.ajax({
                url: presslearn_admin.api_url + '/activate',
                method: 'POST',
                data: {
                    key: key
                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', presslearn_admin.nonce);
                },
                success: function(response) {
                    if (response.success) {
                        PressLearnPlugin.activatePluginUI();
                        PressLearnPlugin.showSuccessMessage(response.message);
                    } else {
                        PressLearnPlugin.showErrorMessage('활성화에 실패했습니다.');
                    }
                },
                error: function(xhr) {
                    var errorMessage = '오류가 발생했습니다.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    PressLearnPlugin.showErrorMessage(errorMessage);
                }
            });
        },
        
        activatePluginUI: function() {
            this.$loginButton.parent().hide();
            
            this.$statusSection.html(
                '<div class="notice notice-success inline">' +
                '<p>✅ 플러그인이 활성화되었습니다. 모든 기능을 사용할 수 있습니다.</p>' +
                '</div>'
            );
            
            $('.presslearn-activated-content').show();
        },
        
        showSuccessMessage: function(message) {
            $('<div class="notice notice-success is-dismissible"><p>' + message + '</p></div>')
                .insertBefore('.wrap h1')
                .delay(5000)
                .fadeOut(500, function() {
                    $(this).remove();
                });
        },
        
        showErrorMessage: function(message) {
            $('<div class="notice notice-error is-dismissible"><p>' + message + '</p></div>')
                .insertBefore('.wrap h1')
                .delay(5000)
                .fadeOut(500, function() {
                    $(this).remove();
                });
        }
    };
    
    $(document).ready(function() {
        PressLearnPlugin.init();
    });
    
})(jQuery); 