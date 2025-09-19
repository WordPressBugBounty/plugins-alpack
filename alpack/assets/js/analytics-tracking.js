/**
 * PressLearn Analytics Tracking
 * 
 */

(function($) {
    'use strict';

    function getVisitorId() {
        let visitorId = localStorage.getItem('presslearn_visitor_id');
        if (!visitorId) {
            if (window.crypto && window.crypto.getRandomValues) {
                const array = new Uint32Array(4);
                window.crypto.getRandomValues(array);
                visitorId = Array.from(array, x => x.toString(36).substr(2, 8)).join('');
            } else {
                visitorId = Math.random().toString(36).substring(2, 15) + 
                    Math.random().toString(36).substring(2, 15);
            }
            localStorage.setItem('presslearn_visitor_id', visitorId);
        }
        return visitorId;
    }


    function getReferrer() {
        if (document.referrer) {
            const currentHost = window.location.hostname;
            try {
                let referrerUrl;
                try {
                    referrerUrl = new URL(document.referrer);
                } catch (e) {
                    return '';
                }
                
                if (referrerUrl.hostname === currentHost) {
                    return '';
                }
                return document.referrer;
            } catch(e) {
                return '';
            }
        }
        return '';
    }

    function trackPageview() {
        if (window.location.href.includes('/wp-admin/')) {
            return;
        }

        if (typeof pressleanAnalytics === 'undefined') {
            return;
        }

        const data = {
            action: 'presslearn_track_pageview',
            visitor_id: getVisitorId(),
            url: window.location.href,
            title: document.title,
            referrer: getReferrer(),
            nonce: pressleanAnalytics.nonce
        };

        $.ajax({
            url: pressleanAnalytics.ajaxurl,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    console.debug('Analytics tracking successful');
                } else {
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }

    $(document).ready(function() {
        try {
            trackPageview();
        } catch (e) {
        }
    });

})(jQuery); 