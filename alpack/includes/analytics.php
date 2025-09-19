<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Analytics functionality for PressLearn Plugin
 */

function presslearn_is_plugin_active_for_analytics() {
    return get_option('presslearn_analytics_enabled', 'no') === 'yes';
}

/**
 * Analytics admin styles
 */
function presslearn_analytics_admin_styles() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-analytics') === false) {
        return;
    }
    
    wp_register_style(
        'presslearn-analytics-admin-css',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION
    );
    wp_enqueue_style('presslearn-analytics-admin-css');
    
    $analytics_css = "
    .analytics-header-stats {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: 15px;
        margin-bottom: 30px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 20px;
    }

    .stats-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        padding: 20px;
        flex: 1;
        min-width: 200px;
        transition: all 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .stats-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transform: translateY(-2px);
    }

    .stats-card h3 {
        font-size: 14px;
        color: #666;
        margin: 0 0 10px 0;
        font-weight: normal;
    }

    .stats-card .stats-value {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }

    .stats-card .stats-change {
        font-size: 13px;
        margin-top: 10px;
        display: block;
    }

    .stats-change.positive {
        color: #4CAF50;
    }

    .stats-change.negative {
        color: #f44336;
    }

    .stats-change.neutral {
        color: #607D8B;
    }

    @media (max-width: 1200px) {
        .stats-card {
            min-width: 180px;
        }
    }

    @media (max-width: 768px) {
        .analytics-header-stats {
            flex-direction: column;
        }
    }

    .date-range-selector {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 20px;
        border: 1px solid #f0f0f0;
    }

    .period-buttons {
        display: flex;
        gap: 8px;
        margin-right: 20px;
    }

    .period-btn, .point-btn {
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 8px 14px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .period-btn:hover, .point-btn:hover {
        background: #e9e9e9;
    }

    .period-btn.active {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .point-btn {
        background: #3498db;
        color: white;
        border-color: #3498db;
    }

    .point-btn:hover {
        background: #2980b9;
    }

    .date-inputs {
        display: flex;
        align-items: center;
        flex: 1;
        gap: 10px;
    }

    .date-inputs input[type='date'] {
        border: 1px solid #ddd;
        padding: 7px 10px;
        border-radius: 5px;
    }

    @media (max-width: 768px) {
        .date-range-selector {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .period-buttons {
            margin-bottom: 15px;
            margin-right: 0;
        }
        
        .date-inputs {
            flex-direction: column;
            align-items: flex-start;
            width: 100%;
        }
        
        .date-inputs input[type='date'] {
            width: 100%;
        }
    }

    .date-range-picker-container {
        float: right;
        margin-left: 20px;
        margin-top: -2px;
    }

    .chart-period-tabs {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 10px;
    }

    .chart-tab {
        display: inline-block;
        padding: 8px 16px;
        text-decoration: none;
        color: #000;
        background-color: #f8f9fa;
        border: 1px solid #b5bfc9;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .chart-tab.active {
        background-color: #2196F3;
        color: #fff;
        border-color: #2196F3;
    }

    .chart-tab:hover:not(.active) {
        background-color: #e9ecef;
    }

    .referrer-chart-container {
        min-height: 300px;
        height: auto;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }

    @media screen and (max-width: 782px) {
        .chart-period-tabs {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .period-buttons {
            display: flex;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .chart-tab {
            flex: 1;
            text-align: center;
        }
        
        .date-range-picker-container {
            float: none;
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
        }
        
        .date-inputs {
            width: 100%;
        }
        
        .date-inputs input[type=\"date\"] {
            flex: 1;
        }
        
        .referrer-chart-container {
            min-height: 250px;
            padding: 10px;
        }
    }
    ";
    
    wp_add_inline_style('presslearn-analytics-admin-css', $analytics_css);
}

/**
 * Analytics admin scripts
 */
function presslearn_analytics_admin_scripts() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-analytics') === false) {
        return;
    }
    
    wp_register_script(
        'presslearn-analytics-referrer-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-analytics-referrer-js');
    
    $referrer_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.toggle-details');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                const detailsRow = document.getElementById('details-' + index);
                const icon = this.querySelector('.dashicons');
                
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = 'table-row';
                    icon.classList.remove('dashicons-plus');
                    icon.classList.add('dashicons-minus');
                } else {
                    detailsRow.style.display = 'none';
                    icon.classList.remove('dashicons-minus');
                    icon.classList.add('dashicons-plus');
                }
            });
        });
    });
    ";
    
    wp_add_inline_script('presslearn-analytics-referrer-js', $referrer_js);

    wp_register_script(
        'presslearn-analytics-date-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-analytics-date-js');
    
    $date_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const todayBtn = document.getElementById('today-btn');
        const chartTabs = document.querySelectorAll('.chart-tab');
        
        if (startDateInput && endDateInput) {
            startDateInput.addEventListener('change', function() {
                endDateInput.min = this.value;
            });
            
            endDateInput.addEventListener('change', function() {
                startDateInput.max = this.value;
            });
        }
        
        if (todayBtn) {
            todayBtn.addEventListener('click', function() {
                const today = new Date().toISOString().split('T')[0];
                if (startDateInput && endDateInput) {
                    startDateInput.value = today;
                    endDateInput.value = today;
                    document.getElementById('date-range-form').submit();
                }
            });
        }
        
        chartTabs.forEach(function(tab) {
            if (tab.href && tab.href.includes('chart_period=')) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const today = new Date();
                    const todayStr = today.toISOString().split('T')[0];
                    let startDate = todayStr;
                    
                    if (this.href.includes('chart_period=daily')) {
                        const startDateObj = new Date(today);
                        startDateObj.setDate(today.getDate() - 30);
                        startDate = startDateObj.toISOString().split('T')[0];
                    } else if (this.href.includes('chart_period=weekly')) {
                        const startDateObj = new Date(today);
                        startDateObj.setDate(today.getDate() - 90);
                        startDate = startDateObj.toISOString().split('T')[0];
                    } else if (this.href.includes('chart_period=yearly')) {
                        const startDateObj = new Date(today);
                        startDateObj.setFullYear(today.getFullYear() - 3);
                        startDate = startDateObj.toISOString().split('T')[0];
                    }
                    
                    let newUrl = this.href;
                    if (newUrl.includes('start_date=') || newUrl.includes('end_date=')) {
                        newUrl = newUrl.replace(/[&?]start_date=[^&]*/g, '');
                        newUrl = newUrl.replace(/[&?]end_date=[^&]*/g, '');
                    }
                    
                    const separator = newUrl.includes('?') ? '&' : '?';
                    newUrl += separator + 'start_date=' + startDate + '&end_date=' + todayStr;
                    
                    window.location.href = newUrl;
                });
            }
        });
    });
    ";
    
    wp_add_inline_script('presslearn-analytics-date-js', $date_js);

    wp_register_script(
        'presslearn-analytics-banner-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-analytics-banner-js');
    
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
            
            fetch('" . esc_url(rest_url('presslearn/v1/banner')) . "', {
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
                    
                    bannerSkeleton.style.display = 'none';
                    banner.style.display = 'block';
                } else {
                    bannerContainer.style.display = 'none';
                }
            })
            .catch(error => {
                console.log('Banner load failed:', error);
                bannerContainer.style.display = 'none';
            });
        }
        
        if (bannerContainer && banner && bannerSkeleton && bannerLink && bannerImage) {
            bannerSkeleton.style.display = 'block';
            banner.style.display = 'none';
            
            setTimeout(fetchBanner, 100);
            setInterval(fetchBanner, 300000);
        }
    });
    ";
    
    wp_add_inline_script('presslearn-analytics-banner-js', $banner_js);

    wp_register_script(
        'presslearn-analytics-ajax-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-analytics-ajax-js');
    
    wp_localize_script('presslearn-analytics-ajax-js', 'presslearn_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('presslearn_delete_analytics_data_nonce')
    ));
    
    $ajax_js = "
    document.addEventListener('DOMContentLoaded', function() {
        function confirmDeleteAnalyticsData() {
            if (confirm('정말로 모든 통계 데이터를 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
                jQuery.ajax({
                    url: presslearn_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'presslearn_delete_analytics_data',
                        nonce: presslearn_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('통계 데이터가 성공적으로 삭제되었습니다.');
                            location.reload();
                        } else {
                            alert('데이터 삭제 중 오류가 발생했습니다: ' + (response.data || '알 수 없는 오류'));
                        }
                    },
                    error: function() {
                        alert('서버와의 통신 중 오류가 발생했습니다.');
                    }
                });
            }
        }
        
        const deleteBtn = document.getElementById('delete-analytics-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', confirmDeleteAnalyticsData);
        }
    });
    ";
    
    wp_add_inline_script('presslearn-analytics-ajax-js', $ajax_js);
}

function presslearn_render_analytics_scripts($chart_data, $chart_period, $referrers_to_display) {
    wp_register_script(
        'presslearn-analytics-chart-render-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-analytics-chart-render-js');
    
    $chart_js = "
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        document.getElementById('today-btn').addEventListener('click', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').value = today;
            document.getElementById('end_date').value = today;
            document.getElementById('date-range-form').submit();
        });
        
        const labels = " . wp_json_encode($chart_data['labels']) . ";
        const pageviewsData = " . wp_json_encode($chart_data['pageviews']) . ";
        const visitorsData = " . wp_json_encode($chart_data['visitors']) . ";
        const ipVisitorsData = " . wp_json_encode($chart_data['ip_visitors']) . ";
        const rawDates = " . wp_json_encode($chart_data['raw_dates']) . ";
        
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        const analyticsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: '페이지뷰',
                        data: pageviewsData,
                        borderColor: '#2196F3',
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) {
                                return 'rgba(33, 150, 243, 0.1)';
                            }
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(33, 150, 243, 0.8)');
                            gradient.addColorStop(1, 'rgba(33, 150, 243, 0.02)');
                            return gradient;
                        },
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    },
                    {
                        label: '방문자 수',
                        data: visitorsData,
                        borderColor: '#4CAF50',
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) {
                                return 'rgba(76, 175, 80, 0.1)';
                            }
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(76, 175, 80, 0.8)');
                            gradient.addColorStop(1, 'rgba(76, 175, 80, 0.02)');
                            return gradient;
                        },
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    },
                    {
                        label: '순 방문자 (IP 기준)',
                        data: ipVisitorsData,
                        borderColor: '#FF9A99',
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) {
                                return 'rgba(255, 154, 150, 0.1)';
                            }
                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                            gradient.addColorStop(0, 'rgba(255, 154, 150, 0.8)');
                            gradient.addColorStop(1, 'rgba(255, 154, 150, 0.02)');
                            return gradient;
                        },
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                hover: {
                    mode: 'index',
                    intersect: false
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: function() {
                                if (rawDates.length > 0 && rawDates[0].period === 'hourly') {
                                    return 24;
                                } else if ('" . esc_js($chart_period) . "' === 'weekly') {
                                    return 12;
                                } else if ('" . esc_js($chart_period) . "' === 'yearly') {
                                    return 12;
                                } else {
                                    return Math.min(labels.length, window.innerWidth < 768 ? 7 : 15);
                                }
                            }()
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'center',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            font: {
                                size: 12
                            },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        padding: 10,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        },
                        displayColors: true,
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            title: function(tooltipItems) {
                                const idx = tooltipItems[0].dataIndex;
                                if (idx >= 0 && idx < rawDates.length) {
                                    const dateInfo = rawDates[idx];
                                    const period = dateInfo.period;
                                    const startDate = moment(dateInfo.start);
                                    
                                    if (period === 'hourly') {
                                        return startDate.format('YYYY년 M월 D일') + ' ' + startDate.format('H시');
                                    } else if (period === 'daily') {
                                        return startDate.format('YYYY년 M월 D일') + ' ' + getDayOfWeekKorean(startDate.day());
                                    } else if (period === 'weekly') {
                                        const endDate = moment(dateInfo.end);
                                        return startDate.format('YYYY년 M월 D일') + ' ' + getDayOfWeekKorean(startDate.day()) + ' ~ ' + 
                                               endDate.format('M월 D일') + ' ' + getDayOfWeekKorean(endDate.day()) + ' 주';
                                    } else if (period === 'yearly') {
                                        return startDate.format('YYYY년');
                                    }
                                }
                                return tooltipItems[0].label;
                            },
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                            }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('ko-KR').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
        
        function getDayOfWeekKorean(day) {
            const days = ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'];
            return days[day];
        }

        function resizeChart() {
            const container = document.querySelector('.chart-container');
            const canvas = document.getElementById('analyticsChart');
            
            if (container && canvas) {
                canvas.style.height = container.offsetHeight + 'px';
                canvas.style.width = container.offsetWidth + 'px';
            }
        }
        
        window.addEventListener('resize', resizeChart);
        resizeChart();
        
        if (document.getElementById('referrerChart')) {
            const referrerData = " . wp_json_encode($referrers_to_display) . ";
            const referrerLabels = referrerData.map(item => item.referrer);
            const referrerCounts = referrerData.map(item => item.count);
            
            function getPlatformColor(platformName) {
                const platformColors = {
                    '구글': '#4285F4',
                    '네이버': '#03C75A',         
                    '네이버 블로그': '#03C75A',  
                    '다음': '#FF6600',           
                    '빙': '#00BCF2',             
                    '야후': '#720E9E',           
                    '페이스북': '#1877F2',       
                    '인스타그램': '#E4405F',     
                    '트위터': '#1DA1F2',         
                    '유튜브': '#FF0000',         
                    '카카오톡': '#FEE500',       
                    '직접 접속': '#6C757D',      
                    '데이터 없음': '#DEE2E6'     
                };
                
                return platformColors[platformName] || '#6C757D';
            }
            
            const backgroundColors = referrerLabels.map(label => getPlatformColor(label));
            const borderColors = backgroundColors;
            
            const referrerCtx = document.getElementById('referrerChart').getContext('2d');
            new Chart(referrerCtx, {
                type: 'bar',
                data: {
                    labels: referrerLabels,
                    datasets: [{
                        label: '페이지뷰 수',
                        data: referrerCounts,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('ko-KR').format(value);
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                },
                                maxRotation: 0
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'start',
                            labels: {
                                boxWidth: 12,
                                padding: 20,
                                font: {
                                    size: 12
                                },
                                usePointStyle: true,
                                pointStyle: 'rect'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 10,
                            titleFont: {
                                size: 13
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + new Intl.NumberFormat('ko-KR').format(context.parsed.x) + ' PV';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    ";
    
    wp_add_inline_script('presslearn-analytics-chart-render-js', $chart_js);
}

add_action('admin_enqueue_scripts', 'presslearn_analytics_admin_styles');
add_action('admin_enqueue_scripts', 'presslearn_analytics_admin_scripts');

