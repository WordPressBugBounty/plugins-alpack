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
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #eee;
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
            });
        }
        
        if (modalMessageInput) {
            modalMessageInput.addEventListener('input', function() {
                const previewMessage = document.getElementById('preview-message');
                if (previewMessage) previewMessage.textContent = this.value;
            });
        }
        
        if (modalSubmessageInput) {
            modalSubmessageInput.addEventListener('input', function() {
                const previewSubmessage = document.getElementById('preview-submessage');
                if (previewSubmessage) previewSubmessage.textContent = this.value;
            });
        }
        
        if (modalButtonTextInput) {
            modalButtonTextInput.addEventListener('input', function() {
                const previewButton = document.getElementById('preview-button');
                if (previewButton) previewButton.textContent = this.value;
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
                    const existingInputs = form.querySelectorAll('input[name^=\"modal_\"]');
                    existingInputs.forEach(input => input.remove());
                    
                    const inputs = [
                        {name: 'modal_title', value: modalTitle},
                        {name: 'modal_message', value: modalMessage},
                        {name: 'modal_submessage', value: modalSubmessage},
                        {name: 'modal_button_text', value: modalButtonText}
                    ];
                    
                    inputs.forEach(inputData => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = inputData.name;
                        input.value = inputData.value;
                        form.appendChild(input);
                    });
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
