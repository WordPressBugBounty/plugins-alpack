<?php
if (!defined('ABSPATH')) {
    exit;
}

function presslearn_is_plugin_active_for_protection() {
    $click_protection_enabled = get_option('presslearn_click_protection_enabled', 'no');
    return $click_protection_enabled === 'yes';
}

/**
 * Protection admin styles
 */
function presslearn_protection_admin_styles() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-click-protection') === false) {
        return;
    }
    
    wp_register_style(
        'presslearn-protection-admin-css',
        false,
        array(),
        PRESSLEARN_PLUGIN_VERSION
    );
    wp_enqueue_style('presslearn-protection-admin-css');
    
    $protection_css = "
    .country-tag-container {
        display: flex;
        width: 100%;
        flex-wrap: wrap;
        gap: 8px;
        flex: 1;
        min-height: 100px;
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        align-items: flex-start;
        box-sizing: border-box;
    }
    
    .country-tag {
        display: inline-flex;
        align-items: center;
        background: #2196F3;
        color: white;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 13px;
        gap: 6px;
    }
    
    .country-tag .country-code {
        background: rgba(255, 255, 255, 0.2);
        padding: 2px 6px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: bold;
    }
    
    .country-tag .remove-country {
        cursor: pointer;
        margin-left: 4px;
        font-weight: bold;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .country-tag .remove-country:hover {
        opacity: 1;
    }
    
    .country-autocomplete {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        z-index: 1000;
        display: none;
    }
    
    .country-autocomplete-item {
        padding: 10px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    
    .country-autocomplete-item:hover,
    .country-autocomplete-item.active {
        background: #f0f0f0;
    }
    
    .country-name {
        flex: 1;
    }
    
    .country-code-badge {
        background: #e0e0e0;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 12px;
        color: #666;
    }
    
    .ip-add-form {
        position: relative;
    }
    
    #blocked-country-input {
        width: 100%; 
    }
    
    .presslearn-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .presslearn-modal-content {
        background-color: #fff;
        width: 90%;
        max-width: 700px;
        border-radius: 6px;
        box-shadow: 0 3px 20px rgba(0, 0, 0, 0.2);
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }

    .presslearn-modal-header {
        padding: 20px;
        border-bottom: 1px solid #ddd;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .presslearn-modal-header h3 {
        margin: 0;
        font-size: 20px;
    }

    .presslearn-modal-close {
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #666;
    }

    .presslearn-modal-close:hover {
        color: #000;
    }

    .presslearn-modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1;
    }

    .presslearn-modal-footer {
        padding: 15px 20px;
        border-top: 1px solid #ddd;
        text-align: right;
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .settings-form-row {
        margin-bottom: 20px;
    }

    .settings-form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .settings-form-row input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .modal-preview {
        margin-top: 30px;
        border-top: 1px solid #eee;
        padding-top: 20px;
    }

    .modal-preview h4 {
        margin-top: 0;
        margin-bottom: 15px;
    }

    .modal-preview-container {
        border: 1px dashed #ccc;
        padding: 20px;
        border-radius: 5px;
    }

    .ip-add-form {
        gap: 20px;
        margin-bottom: 20px;
        flex-direction: column;
    }

    .ip-input-group {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .ip-input-group input {
        flex: 1;
    }

    .ip-description {
        color: #666;
        font-size: 13px;
        margin: 5px 0 0;
    }

    .ip-list-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ip-list-table th, 
    .ip-list-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .ip-list-table th {
        background-color: #f9f9f9;
        font-weight: 600;
    }

    .ip-list-table tr:hover {
        background-color: #f5f5f5;
    }

    .delete-ip,
    .delete-blocked-ip {
        color: #d32f2f;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }

    .delete-ip:hover,
    .delete-blocked-ip:hover {
        text-decoration: underline;
    }

    .log-filter {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        align-items: center;
    }

    .log-list-table {
        width: 100%;
        border-collapse: collapse;
    }

    .log-list-table th, 
    .log-list-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .log-list-table th {
        background-color: #f9f9f9;
        font-weight: 600;
    }

    .log-list-table tr:hover {
        background-color: #f5f5f5;
    }

    .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 50px;
    }

    .log-expired {
        color: #999;
        text-decoration: line-through;
    }

    .log-active {
        color: #d32f2f;
    }
    ";
    
    wp_add_inline_style('presslearn-protection-admin-css', $protection_css);
}

/**
 * Protection admin scripts
 */
function presslearn_protection_admin_scripts() {
    if (!is_admin()) {
        return;
    }
    
    $screen = get_current_screen();
    if (!$screen || strpos($screen->id, 'presslearn-click-protection') === false) {
        return;
    }
    
    wp_register_script(
        'presslearn-protection-ip-management-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-protection-ip-management-js');
    
    wp_localize_script('presslearn-protection-ip-management-js', 'presslearn_protection_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('presslearn_ip_nonce')
    ));
    
    $ip_management_js = "
    document.addEventListener('DOMContentLoaded', function() {
        let allowedIPs = [];
        let blockedIPs = [];
        
        const ipNonce = presslearn_protection_ajax.nonce;
        const ajaxUrl = presslearn_protection_ajax.ajax_url;
        
        const openAllowedBtn = document.getElementById('open-allowed-ip-editor');
        const openBlockedBtn = document.getElementById('open-blocked-ip-editor');
        const openBlockedCountriesBtn = document.getElementById('open-blocked-countries-editor');
        
        if (openAllowedBtn) {
            openAllowedBtn.addEventListener('click', function() {
                loadAllowedIPs();
                document.getElementById('allowed-ip-modal').style.display = 'flex';
            });
        }
        
        if (openBlockedBtn) {
            openBlockedBtn.addEventListener('click', function() {
                loadBlockedIPs();
                document.getElementById('blocked-ip-modal').style.display = 'flex';
            });
        }
        
        if (openBlockedCountriesBtn) {
            openBlockedCountriesBtn.addEventListener('click', function() {
                loadBlockedCountries();
                document.getElementById('blocked-countries-editor').style.display = 'flex';
            });
        }
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('presslearn-modal-close') || 
                e.target.id === 'close-modal-button') {
                document.getElementById('allowed-ip-modal').style.display = 'none';
            }
            if (e.target.classList.contains('presslearn-modal-close') || 
                e.target.id === 'close-blocked-modal-button') {
                document.getElementById('blocked-ip-modal').style.display = 'none';
            }
            if (e.target.id === 'allowed-ip-modal') {
                document.getElementById('allowed-ip-modal').style.display = 'none';
            }
            if (e.target.id === 'blocked-ip-modal') {
                document.getElementById('blocked-ip-modal').style.display = 'none';
            }
        });
        
        const addIpBtn = document.getElementById('add-ip-button');
        const addBlockedIpBtn = document.getElementById('add-blocked-ip-button');
        const newIpInput = document.getElementById('new-ip-address');
        const newBlockedIpInput = document.getElementById('new-blocked-ip');
        
        if (addIpBtn) {
            addIpBtn.addEventListener('click', addNewIP);
        }
        
        if (addBlockedIpBtn) {
            addBlockedIpBtn.addEventListener('click', addNewBlockedIP);
        }
        
        if (newIpInput) {
            newIpInput.addEventListener('keypress', function(e) {
                if (e.which === 13) {
                    addNewIP();
                    e.preventDefault();
                }
            });
        }
        
        if (newBlockedIpInput) {
            newBlockedIpInput.addEventListener('keypress', function(e) {
                if (e.which === 13) {
                    addNewBlockedIP();
                    e.preventDefault();
                }
            });
        }
        
        function loadAllowedIPs() {
            const tbody = document.getElementById('allowed-ip-list');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan=\"3\" style=\"text-align:center;\"><div class=\"spinner-container\"><span class=\"spinner is-active\"></span></div></td></tr>';
            }
            
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'presslearn_get_allowed_ips',
                    nonce: ipNonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allowedIPs = data.data.ips || [];
                    renderIPTable();
                } else {
                    alert('허용 IP 목록을 불러오는데 실패했습니다.');
                    allowedIPs = [];
                    renderIPTable();
                }
            })
            .catch(error => {
                alert('서버 통신 오류가 발생했습니다.');
                allowedIPs = [];
                renderIPTable();
            });
        }
        
        function loadBlockedIPs() {
            const tbody = document.getElementById('blocked-ip-list');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan=\"3\" style=\"text-align:center;\"><div class=\"spinner-container\"><span class=\"spinner is-active\"></span></div></td></tr>';
            }
            
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'presslearn_get_blocked_ips',
                    nonce: ipNonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    blockedIPs = data.data.ips || [];
                    renderBlockedIPTable();
                } else {
                    alert('차단 IP 목록을 불러오는데 실패했습니다.');
                    blockedIPs = [];
                    renderBlockedIPTable();
                }
            })
            .catch(error => {
                alert('서버 통신 오류가 발생했습니다.');
                blockedIPs = [];
                renderBlockedIPTable();
            });
        }
        
        function renderIPTable() {
            const tbody = document.getElementById('allowed-ip-list');
            const noIpsMessage = document.getElementById('no-ips-message');
            
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (allowedIPs.length > 0) {
                if (noIpsMessage) noIpsMessage.style.display = 'none';
                
                allowedIPs.forEach((item) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>\${item.ip}</td>
                        <td>\${item.date}</td>
                        <td>
                            <button type=\"button\" class=\"delete-ip\" data-ip=\"\${item.ip}\">삭제</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
                
                document.querySelectorAll('.delete-ip').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const ip = this.getAttribute('data-ip');
                        deleteIP(ip);
                    });
                });
            } else {
                if (noIpsMessage) noIpsMessage.style.display = 'block';
            }
        }
        
        function renderBlockedIPTable() {
            const tbody = document.getElementById('blocked-ip-list');
            const noBlockedIpsMessage = document.getElementById('no-blocked-ips-message');
            
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (blockedIPs.length > 0) {
                if (noBlockedIpsMessage) noBlockedIpsMessage.style.display = 'none';
                
                blockedIPs.forEach((item) => {
                    const row = document.createElement('tr');
                    const expiryText = item.expires ? item.expires : '영구 차단';
                    const reasonText = item.reason || '수동 차단';
                    
                    row.innerHTML = `
                        <td>\${item.ip}</td>
                        <td>\${item.date}</td>
                        <td>
                            <button type=\"button\" class=\"delete-blocked-ip\" data-ip=\"\${item.ip}\" title=\"이 IP를 차단 목록에서 제거하고 로컬 스토리지를 리셋합니다\">삭제</button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
                
                document.querySelectorAll('.delete-blocked-ip').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const ip = this.getAttribute('data-ip');
                        deleteBlockedIP(ip);
                    });
                });
            } else {
                if (noBlockedIpsMessage) noBlockedIpsMessage.style.display = 'block';
            }
        }
        
        function addNewIP() {
            const newIpInput = document.getElementById('new-ip-address');
            if (!newIpInput) return;
            
            const newIP = newIpInput.value.trim();
            
            if (!isValidIP(newIP)) {
                alert('유효한 IP 주소를 입력해주세요.');
                return;
            }
            
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'presslearn_add_allowed_ip',
                    nonce: ipNonce,
                    ip: newIP
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('IP가 성공적으로 허용 목록에 추가되었습니다.');
                    allowedIPs = data.data.ips;
                    renderIPTable();
                    newIpInput.value = '';
                    newIpInput.focus();
                } else {
                    alert(data.data.message || '허용 IP 추가에 실패했습니다.');
                }
            })
            .catch(error => {
                alert('서버 통신 오류가 발생했습니다.');
            });
        }
        
        function addNewBlockedIP() {
            const newBlockedIpInput = document.getElementById('new-blocked-ip');
            const permanentBlockCheckbox = document.getElementById('permanent-block-checkbox');
            if (!newBlockedIpInput) return;
            
            const newIP = newBlockedIpInput.value.trim();
            const isPermanent = permanentBlockCheckbox ? permanentBlockCheckbox.checked : false;
            
            if (!isValidIP(newIP)) {
                alert('유효한 IP 주소를 입력해주세요.');
                return;
            }
            
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'presslearn_add_blocked_ip',
                    nonce: ipNonce,
                    ip: newIP,
                    permanent: isPermanent ? '1' : '0'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('IP가 성공적으로 차단되었습니다.');
                    blockedIPs = data.data.ips;
                    renderBlockedIPTable();
                    newBlockedIpInput.value = '';
                    if (permanentBlockCheckbox) permanentBlockCheckbox.checked = false;
                    newBlockedIpInput.focus();
                } else {
                    alert(data.data.message || '차단 IP 추가에 실패했습니다.');
                }
            })
            .catch(error => {
                alert('서버 통신 오류가 발생했습니다.');
            });
        }
        
        function deleteIP(ip) {
            if (confirm('이 IP를 허용 목록에서 삭제하시겠습니까?')) {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'presslearn_delete_allowed_ip',
                        nonce: ipNonce,
                        ip: ip
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('IP가 성공적으로 허용 목록에서 삭제되었습니다.');
                        allowedIPs = data.data.ips;
                        renderIPTable();
                    } else {
                        alert(data.data.message || '허용 IP 삭제에 실패했습니다.');
                    }
                })
                .catch(error => {
                    alert('서버 통신 오류가 발생했습니다.');
                });
            }
        }
        
        function deleteBlockedIP(ip) {
            if (confirm('이 IP를 차단 목록에서 삭제하시겠습니까?')) {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'presslearn_delete_blocked_ip',
                        nonce: ipNonce,
                        ip: ip
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('IP가 성공적으로 차단 목록에서 삭제되었습니다.');
                        blockedIPs = data.data.ips;
                        renderBlockedIPTable();
                    } else {
                        alert(data.data.message || '차단 IP 삭제에 실패했습니다.');
                    }
                })
                .catch(error => {
                    alert('서버 통신 오류가 발생했습니다.');
                });
            }
        }
        
        function isValidIP(ip) {
            const regex = /^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
            return regex.test(ip);
        }
        
        // 국가별 차단 관련 코드
        const countries = [
            { name: '대한민국', code: 'KR' },
            { name: '북한', code: 'KP' },
            { name: '중국', code: 'CN' },
            { name: '일본', code: 'JP' },
            { name: '미국', code: 'US' },
            { name: '러시아', code: 'RU' },
            { name: '베트남', code: 'VN' },
            { name: '태국', code: 'TH' },
            { name: '인도네시아', code: 'ID' },
            { name: '필리핀', code: 'PH' },
            { name: '말레이시아', code: 'MY' },
            { name: '싱가포르', code: 'SG' },
            { name: '인도', code: 'IN' },
            { name: '파키스탄', code: 'PK' },
            { name: '방글라데시', code: 'BD' },
            { name: '네팔', code: 'NP' },
            { name: '스리랑카', code: 'LK' },
            { name: '몽골', code: 'MN' },
            { name: '대만', code: 'TW' },
            { name: '홍콩', code: 'HK' },
            { name: '마카오', code: 'MO' },
            { name: '캐나다', code: 'CA' },
            { name: '멕시코', code: 'MX' },
            { name: '브라질', code: 'BR' },
            { name: '아르헨티나', code: 'AR' },
            { name: '칠레', code: 'CL' },
            { name: '페루', code: 'PE' },
            { name: '콜롬비아', code: 'CO' },
            { name: '베네수엘라', code: 'VE' },
            { name: '영국', code: 'GB' },
            { name: '프랑스', code: 'FR' },
            { name: '독일', code: 'DE' },
            { name: '이탈리아', code: 'IT' },
            { name: '스페인', code: 'ES' },
            { name: '네덜란드', code: 'NL' },
            { name: '벨기에', code: 'BE' },
            { name: '스위스', code: 'CH' },
            { name: '스웨덴', code: 'SE' },
            { name: '노르웨이', code: 'NO' },
            { name: '덴마크', code: 'DK' },
            { name: '핀란드', code: 'FI' },
            { name: '폴란드', code: 'PL' },
            { name: '체코', code: 'CZ' },
            { name: '헝가리', code: 'HU' },
            { name: '루마니아', code: 'RO' },
            { name: '불가리아', code: 'BG' },
            { name: '그리스', code: 'GR' },
            { name: '터키', code: 'TR' },
            { name: '우크라이나', code: 'UA' },
            { name: '벨라루스', code: 'BY' },
            { name: '카자흐스탄', code: 'KZ' },
            { name: '우즈베키스탄', code: 'UZ' },
            { name: '이란', code: 'IR' },
            { name: '이라크', code: 'IQ' },
            { name: '사우디아라비아', code: 'SA' },
            { name: '아랍에미리트', code: 'AE' },
            { name: '이스라엘', code: 'IL' },
            { name: '이집트', code: 'EG' },
            { name: '남아프리카', code: 'ZA' },
            { name: '나이지리아', code: 'NG' },
            { name: '케냐', code: 'KE' },
            { name: '에티오피아', code: 'ET' },
            { name: '가나', code: 'GH' },
            { name: '모로코', code: 'MA' },
            { name: '알제리', code: 'DZ' },
            { name: '튀니지', code: 'TN' },
            { name: '호주', code: 'AU' },
            { name: '뉴질랜드', code: 'NZ' },
            { name: '피지', code: 'FJ' }
        ];
        
        let blockedCountries = [];
        let activeAutocompleteIndex = -1;
        
        const countryInput = document.getElementById('blocked-country-input');
        const countryAutocomplete = document.getElementById('country-autocomplete');
        const countryTagsContainer = document.getElementById('blocked-countries-tags');
        const saveBlockedCountriesBtn = document.getElementById('save-blocked-countries');
        const closeCountriesModalBtn = document.getElementById('close-blocked-countries-modal');
        const blockedCountriesModal = document.getElementById('blocked-countries-editor');
        
        if (closeCountriesModalBtn) {
            closeCountriesModalBtn.addEventListener('click', function() {
                blockedCountriesModal.style.display = 'none';
            });
        }
        
        if (blockedCountriesModal) {
            blockedCountriesModal.addEventListener('click', function(e) {
                if (e.target === blockedCountriesModal || e.target.classList.contains('presslearn-modal-close')) {
                    blockedCountriesModal.style.display = 'none';
                }
            });
        }
        
        function loadBlockedCountries() {
            fetch(ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'presslearn_get_blocked_countries',
                    nonce: ipNonce
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.countries) {
                    blockedCountries = data.data.countries;
                    renderCountryTags();
                }
            });
        }
        
        function renderCountryTags() {
            countryTagsContainer.innerHTML = '';
            blockedCountries.forEach(country => {
                const tag = document.createElement('span');
                tag.className = 'country-tag';
                tag.innerHTML = 
                    country.name +
                    '<span class=\"country-code\">' + country.code + '</span>' +
                    '<span class=\"remove-country\" data-code=\"' + country.code + '\">×</span>';
                countryTagsContainer.appendChild(tag);
            });
        }
        
        function showAutocomplete(searchTerm) {
            const filtered = countries.filter(country => 
                country.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                country.code.toLowerCase().includes(searchTerm.toLowerCase())
            ).filter(country => 
                !blockedCountries.some(blocked => blocked.code === country.code)
            );
            
            countryAutocomplete.innerHTML = '';
            activeAutocompleteIndex = -1;
            
            if (filtered.length > 0 && searchTerm.length > 0) {
                countryAutocomplete.style.display = 'block';
                filtered.forEach((country, index) => {
                    const item = document.createElement('div');
                    item.className = 'country-autocomplete-item';
                    item.setAttribute('data-index', index);
                    item.innerHTML = 
                        '<span class=\"country-name\">' + country.name + '</span>' +
                        '<span class=\"country-code-badge\">' + country.code + '</span>';
                    item.addEventListener('click', () => selectCountry(country));
                    countryAutocomplete.appendChild(item);
                });
            } else {
                countryAutocomplete.style.display = 'none';
            }
        }
        
        function selectCountry(country) {
            if (!blockedCountries.some(blocked => blocked.code === country.code)) {
                blockedCountries.push(country);
                renderCountryTags();
                countryInput.value = '';
                countryAutocomplete.style.display = 'none';
            }
        }
        
        if (countryInput) {
            countryInput.addEventListener('input', (e) => {
                showAutocomplete(e.target.value);
            });
            
            countryInput.addEventListener('keydown', (e) => {
                const items = countryAutocomplete.querySelectorAll('.country-autocomplete-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeAutocompleteIndex = Math.min(activeAutocompleteIndex + 1, items.length - 1);
                    updateActiveItem(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeAutocompleteIndex = Math.max(activeAutocompleteIndex - 1, -1);
                    updateActiveItem(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeAutocompleteIndex >= 0 && items[activeAutocompleteIndex]) {
                        items[activeAutocompleteIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    countryAutocomplete.style.display = 'none';
                    activeAutocompleteIndex = -1;
                }
            });
            
            countryInput.addEventListener('blur', () => {
                setTimeout(() => {
                    countryAutocomplete.style.display = 'none';
                }, 200);
            });
        }
        
        function updateActiveItem(items) {
            items.forEach((item, index) => {
                if (index === activeAutocompleteIndex) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }
        
        if (countryTagsContainer) {
            countryTagsContainer.addEventListener('click', (e) => {
                if (e.target.classList.contains('remove-country')) {
                    const code = e.target.getAttribute('data-code');
                    blockedCountries = blockedCountries.filter(country => country.code !== code);
                    renderCountryTags();
                }
            });
        }
        
        if (saveBlockedCountriesBtn) {
            saveBlockedCountriesBtn.addEventListener('click', () => {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'presslearn_save_blocked_countries',
                        nonce: ipNonce,
                        countries: JSON.stringify(blockedCountries)
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('차단 국가 목록이 저장되었습니다.');
                        blockedCountriesModal.style.display = 'none';
                    } else {
                        alert(data.data.message || '저장에 실패했습니다.');
                    }
                });
            });
        }
    });
    ";
    
    wp_add_inline_script('presslearn-protection-ip-management-js', $ip_management_js);

    wp_register_script(
        'presslearn-protection-modal-settings-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-protection-modal-settings-js');
    
    $modal_settings_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const openModalSettingsBtn = document.getElementById('open-modal-settings');
        const closeModalSettingsBtn = document.getElementById('close-modal-settings');
        const saveModalSettingsBtn = document.getElementById('save-modal-settings');
        
        if (openModalSettingsBtn) {
            openModalSettingsBtn.addEventListener('click', function() {
                document.getElementById('modal-settings-editor').style.display = 'flex';
            });
        }
        
        if (closeModalSettingsBtn) {
            closeModalSettingsBtn.addEventListener('click', function() {
                document.getElementById('modal-settings-editor').style.display = 'none';
            });
        }
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('presslearn-modal-close')) {
                document.getElementById('modal-settings-editor').style.display = 'none';
            }
            if (e.target.id === 'modal-settings-editor') {
                document.getElementById('modal-settings-editor').style.display = 'none';
            }
        });
        
        const modalTitleInput = document.getElementById('modal_title');
        const modalMessageInput = document.getElementById('modal_message');
        const modalSubmessageInput = document.getElementById('modal_submessage');
        const modalButtonTextInput = document.getElementById('modal_button_text');
        
        if (modalTitleInput) {
            modalTitleInput.addEventListener('input', function() {
                const previewTitle = document.getElementById('preview-title');
                if (previewTitle) previewTitle.textContent = this.value;
                const form = document.getElementById('click-protection-settings-form');
                if (form) {
                    const hiddenInput = form.querySelector('input[name=\"modal_title\"]');
                    if (hiddenInput) hiddenInput.value = this.value;
                }
            });
        }
        
        if (modalMessageInput) {
            modalMessageInput.addEventListener('input', function() {
                const previewMessage = document.getElementById('preview-message');
                if (previewMessage) previewMessage.textContent = this.value;
                const form = document.getElementById('click-protection-settings-form');
                if (form) {
                    const hiddenInput = form.querySelector('input[name=\"modal_message\"]');
                    if (hiddenInput) hiddenInput.value = this.value;
                }
            });
        }
        
        if (modalSubmessageInput) {
            modalSubmessageInput.addEventListener('input', function() {
                const previewSubmessage = document.getElementById('preview-submessage');
                if (previewSubmessage) previewSubmessage.textContent = this.value;
                const form = document.getElementById('click-protection-settings-form');
                if (form) {
                    const hiddenInput = form.querySelector('input[name=\"modal_submessage\"]');
                    if (hiddenInput) hiddenInput.value = this.value;
                }
            });
        }
        
        if (modalButtonTextInput) {
            modalButtonTextInput.addEventListener('input', function() {
                const previewButton = document.getElementById('preview-button');
                if (previewButton) previewButton.textContent = this.value;
                const form = document.getElementById('click-protection-settings-form');
                if (form) {
                    const hiddenInput = form.querySelector('input[name=\"modal_button_text\"]');
                    if (hiddenInput) hiddenInput.value = this.value;
                }
            });
        }
        
        if (saveModalSettingsBtn) {
            saveModalSettingsBtn.addEventListener('click', function() {
                const modalTitle = modalTitleInput ? modalTitleInput.value : '';
                const modalMessage = modalMessageInput ? modalMessageInput.value : '';
                const modalSubmessage = modalSubmessageInput ? modalSubmessageInput.value : '';
                const modalButtonText = modalButtonTextInput ? modalButtonTextInput.value : '';
                
                const form = document.getElementById('click-protection-settings-form');
                if (form) {
                    const titleInput = form.querySelector('input[name=\"modal_title\"]');
                    const messageInput = form.querySelector('input[name=\"modal_message\"]');
                    const submessageInput = form.querySelector('input[name=\"modal_submessage\"]');
                    const buttonTextInput = form.querySelector('input[name=\"modal_button_text\"]');
                    
                    if (titleInput) titleInput.value = modalTitle;
                    if (messageInput) messageInput.value = modalMessage;
                    if (submessageInput) submessageInput.value = modalSubmessage;
                    if (buttonTextInput) buttonTextInput.value = modalButtonText;
                }
                
                document.getElementById('modal-settings-editor').style.display = 'none';
                alert('모달 설정이 적용되었습니다. 변경사항을 저장하려면 \"저장하기\" 버튼을 클릭하세요.');
            });
        }
    });
    ";
    
    wp_add_inline_script('presslearn-protection-modal-settings-js', $modal_settings_js);

    wp_register_script(
        'presslearn-protection-blocked-logs-js',
        false,
        array('jquery'),
        PRESSLEARN_PLUGIN_VERSION,
        true
    );
    wp_enqueue_script('presslearn-protection-blocked-logs-js');
    
    wp_localize_script('presslearn-protection-blocked-logs-js', 'presslearn_logs_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('presslearn_ip_nonce')
    ));
    
    $blocked_logs_js = "
    document.addEventListener('DOMContentLoaded', function() {
        const ajaxUrl = presslearn_logs_ajax.ajax_url;
        const ipNonce = presslearn_logs_ajax.nonce;
        
        const openLogModalBtn = document.getElementById('open-blocked-log-modal');
        const closeLogModalBtn = document.getElementById('close-log-modal-button');
        const refreshLogBtn = document.getElementById('refresh-log');
        
        if (openLogModalBtn) {
            openLogModalBtn.addEventListener('click', function() {
                loadBlockedLogs();
                document.getElementById('blocked-log-modal').style.display = 'flex';
            });
        }
        
        if (closeLogModalBtn) {
            closeLogModalBtn.addEventListener('click', function() {
                document.getElementById('blocked-log-modal').style.display = 'none';
            });
        }
        
        if (refreshLogBtn) {
            refreshLogBtn.addEventListener('click', loadBlockedLogs);
        }
        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('presslearn-modal-close')) {
                document.getElementById('blocked-log-modal').style.display = 'none';
            }
            if (e.target.id === 'blocked-log-modal') {
                document.getElementById('blocked-log-modal').style.display = 'none';
            }
        });
        
        function loadBlockedLogs() {
            const logList = document.getElementById('blocked-log-list');
            const noLogsMessage = document.getElementById('no-blocked-logs-message');
            const logLoading = document.getElementById('log-loading');
            const periodSelect = document.getElementById('log-filter-period');
            
            if (logList) logList.innerHTML = '';
            if (noLogsMessage) noLogsMessage.style.display = 'none';
            if (logLoading) logLoading.style.display = 'block';
            
            const period = periodSelect ? periodSelect.value : '30';
            
            fetch(presslearn_logs_ajax.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'presslearn_get_blocked_logs',
                    nonce: presslearn_logs_ajax.nonce,
                    period: period
                })
            })
            .then(response => response.json())
            .then(data => {
                if (logLoading) logLoading.style.display = 'none';
                
                if (data.success) {
                    const logs = data.data.logs || [];
                    renderLogTable(logs);
                } else {
                    alert('차단 로그를 불러오는데 실패했습니다.');
                    if (noLogsMessage) noLogsMessage.style.display = 'block';
                }
            })
            .catch(error => {
                if (logLoading) logLoading.style.display = 'none';
                alert('서버 통신 오류가 발생했습니다.');
                if (noLogsMessage) noLogsMessage.style.display = 'block';
            });
        }
        
        function renderLogTable(logs) {
            const tbody = document.getElementById('blocked-log-list');
            const noLogsMessage = document.getElementById('no-blocked-logs-message');
            
            if (!tbody) return;
            
            tbody.innerHTML = '';
            
            if (logs.length > 0) {
                if (noLogsMessage) noLogsMessage.style.display = 'none';
                
                const today = new Date();
                
                logs.forEach((item) => {
                    let statusClass = 'log-active';
                    let expiryText = item.expires || '영구 차단';
                    let actionButton = '';
                    
                    if (item.expires) {
                        const expiryDate = new Date(item.expires);
                        if (expiryDate < today) {
                            statusClass = 'log-expired';
                            expiryText += ' (만료됨)';
                        } else {
                            actionButton = `<button type=\"button\" class=\"unblock-ip-from-log\" data-ip=\"\${item.ip}\" style=\"color: #d32f2f; background: none; border: none; cursor: pointer; text-decoration: underline;\">해지</button>`;
                        }
                    } else {
                        actionButton = `<button type=\"button\" class=\"unblock-ip-from-log\" data-ip=\"\${item.ip}\" style=\"color: #d32f2f; background: none; border: none; cursor: pointer; text-decoration: underline;\">해지</button>`;
                    }
                    
                    const row = document.createElement('tr');
                    row.className = statusClass;
                    row.innerHTML = `
                        <td>\${item.ip}</td>
                        <td>\${item.block_date || item.date}</td>
                        <td>\${item.reason || '수동 차단'}</td>
                        <td>\${expiryText}</td>
                        <td>\${actionButton}</td>
                    `;
                    tbody.appendChild(row);
                });
                
                document.querySelectorAll('.unblock-ip-from-log').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const ip = this.getAttribute('data-ip');
                        unblockIPFromLog(ip);
                    });
                });
            } else {
                if (noLogsMessage) noLogsMessage.style.display = 'block';
            }
        }
        
        function unblockIPFromLog(ip) {
            if (confirm('이 IP의 차단을 해제하시겠습니까? 해당 IP의 브라우저 제한도 함께 해제됩니다.')) {
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'presslearn_unblock_ip_with_reset',
                        nonce: ipNonce,
                        ip: ip
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('IP 차단이 성공적으로 해제되었습니다.');
                        loadBlockedLogs();
                        if (document.getElementById('blocked-ip-modal').style.display === 'flex') {
                            loadBlockedIPs();
                        }
                    } else {
                        alert(data.data.message || 'IP 차단 해제에 실패했습니다.');
                    }
                })
                .catch(error => {
                    alert('서버 통신 오류가 발생했습니다.');
                });
            }
        }
    });
    ";
    
    wp_add_inline_script('presslearn-protection-blocked-logs-js', $blocked_logs_js);
}

add_action('admin_enqueue_scripts', 'presslearn_protection_admin_styles');
add_action('admin_enqueue_scripts', 'presslearn_protection_admin_scripts');
