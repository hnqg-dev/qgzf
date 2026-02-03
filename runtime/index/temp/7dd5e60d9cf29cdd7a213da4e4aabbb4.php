<?php /*a:2:{s:55:"/wwwroot/website/qgzf/public/template/default/wode.html";i:1761141582;s:55:"/wwwroot/website/qgzf/public/template/default/menu.html";i:1761141118;}*/ ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>我的 - 全哥账房</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/template/default/css/bootstrap.min.css">
    <link rel="stylesheet" href="/template/default/css/qxqlan.css">
    <link rel="stylesheet" href="/template/default/font/qxqicon.css">
    <link rel="stylesheet" href="/template/default/font/bootstrap-icons.min.css">

</head>
<body>
<?php 
use think\facade\Session;
if (!Session::has('user')) {
    header('Location: /index/index/login');
    exit;
}
?>
     <nav class="navbar navbar-expand d-lg-none bg-light mobile-nav">
        <div class="container-fluid justify-content-around">

            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/index/index'); ?>" data-controller="index/index">
                <i class="d-block qxqicon qxq-shouye fs-5 default-link"></i>
                <span class="d-block small">首页</span>
            </a>
            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/zhangben/index'); ?>" data-controller="index/zhangben">
                <i class="d-block qxqicon qxq-zhangben fs-5 default-link"></i>
                <span class="d-block small">账本</span>
            </a>
            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/jiedai/index'); ?>" data-controller="index/jiedai">
                <i class="d-block qxqicon qxq-yinhangkaduoka fs-5 default-link"></i>
                <span class="d-block small">资产</span>
            </a>
            <a class="nav-link text-center px-2 py-1 default-link" href="<?php echo url('index/wode/index'); ?>" data-controller="index/wode">
                <i class="d-block bi bi-person fs-5 default-link"></i>
                <span class="d-block small">我的</span>
            </a>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const path = window.location.pathname;
                const navLinks = document.querySelectorAll('.mobile-nav .nav-link');
                let hasActive = false;
                navLinks.forEach(link => {
                    const controller = link.getAttribute('data-controller');
                    if (path.includes(controller)) {
                        link.classList.remove('default-link');
                        link.classList.add('active', 'active-link');
                        
                        const icon = link.querySelector('i');
                        if (icon) {
                            icon.classList.remove('default-link');
                            icon.classList.add('active-link');
                        }
                        hasActive = true;
                    }
                });
                if (!hasActive) {
                    const homeLink = document.querySelector('.mobile-nav .nav-link[data-controller="index/index"]');
                    if (homeLink) {
                        homeLink.classList.remove('default-link');
                        homeLink.classList.add('active', 'active-link');
                        
                        const icon = homeLink.querySelector('i');
                        if (icon) {
                            icon.classList.remove('default-link');
                            icon.classList.add('active-link');
                        }
                    }
                }
            });
            </script>
        </div>
    </nav>


<div class="container-fluid p-0">
    <div id="topzw"></div>
    <div id="topsz">
        <div class="wo-logo">
            <img src="<?php if($user['ufacesort'] == 1): ?>/upload/face/<?php echo htmlentities((string) (isset($user['uname']) && ($user['uname'] !== '')?$user['uname']:'')); ?>/<?php echo htmlentities((string) $user['uface']); else: ?>/upload/face/sysimg/<?php echo htmlentities((string) $user['uface']); ?><?php endif; ?>" class="rounded-circle" alt="用户头像">
        </div>
        <div class="wo-title" onclick="showEditUserModal()">
            <span class="wo-uname">
                <?php if(!$user['nname']): ?><?php echo htmlentities((string) (isset($user['uname']) && ($user['uname'] !== '')?$user['uname']:'')); else: ?><?php echo htmlentities((string) (isset($user['nname']) && ($user['nname'] !== '')?$user['nname']:'')); ?><?php endif; ?>
            </span>
            <span class="wo-iphone"><?php echo htmlentities((string) (isset($user['uiphone']) && ($user['uiphone'] !== '')?$user['uiphone']:'')); ?></span> <i class="wo-icon bi bi-pencil"></i><br>
            <span class="wo-shiyong">已用：</span><span class="wo-tian"><?php echo htmlentities((string) $user['days_used']); ?>天</span> &nbsp;&nbsp; <span class="wo-ctime">注册日期：<?php echo htmlentities((string) $user['ctime']); ?></span>
        </div>
    </div>
    <div id="szcon">
        <div class="sz-mobile-cards-container">
            <div class="sz-cards-wrapper">
                <div class="sz-card-item" onclick="showEditUserModal()">
                    <div class="sz-custom-card">
                        <i class="qxqicon qxq-gerenxinxi sz-card-icon"></i>
                    </div>
                    <div class="sz-card-text">个人信息</div>
                </div>
                <div class="sz-card-item" onclick="showChangePasswordModal()">
                    <div class="sz-custom-card">
                        <i class="qxqicon qxq-mimashezhi sz-card-icon"></i>
                    </div>
                    <div class="sz-card-text">密码设置</div>
                </div>
                <div class="sz-card-item" onclick="clearCache()">
                    <div class="sz-custom-card">
                        <i class="qxqicon qxq-huojian sz-card-icon"></i>
                    </div>
                    <div class="sz-card-text">清理缓存</div>
                </div>
                <div class="sz-card-item" onclick="logout()">
                    <div class="sz-custom-card">
                        <i class="qxqicon qxq-tuichu sz-card-icon"></i>
                    </div>
                    <div class="sz-card-text">退出登录</div>
                </div>
            </div>
        </div>
         <?php if($isAdmin): ?>
        <div class="xt-settings-title">设置</div>
        <div class="card xt-settings-card">
            <div class="list-group list-group-flush">
                <div class="list-group-item xt-list-group-item d-flex align-items-center" onclick="showEditSysNameModal()">
                    <i class="bi bi bi-gear xt-list-item-icon"></i>
                    <span class="xt-list-item-text">系统设置</span>
                    <i class="bi bi-chevron-right xt-list-item-arrow"></i>
                </div>
                <div class="list-group-item xt-list-group-item d-flex align-items-center" onclick="showEditMbNameModal()">
                    <i class="bi bi-palette xt-list-item-icon"></i>
                    <span class="xt-list-item-text">模板设置</span>
                    <i class="bi bi-chevron-right xt-list-item-arrow"></i>
                </div>
                <div class="list-group-item xt-list-group-item d-flex align-items-center">
                    <i class="bi bi bi-person-plus xt-list-item-icon"></i>
                    <span class="xt-list-item-text">注册开关</span>
                    <div class="form-check form-switch form-switch-sm ms-auto">
                        <input class="form-check-input" type="checkbox" id="regSwitch" 
                               <?php if(!isset($sysSettings['regstate']) || $sysSettings['regstate'] == '0'): ?>checked<?php endif; ?>
                               onchange="toggleRegState(this.checked)">
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="zw-bottom"> </div>
    </div>
</div>
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">修改密码</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="changePasswordForm">
                    <div class="mb-3">
                        <label for="oldPassword" class="form-label">原密码</label>
                        <input type="password" class="form-control" id="oldPassword" name="old_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">新密码</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">确认新密码</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                    </div>
                    <input type="hidden" name="__token__" value="<?php echo token(); ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitChangePassword()">确认修改</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editSysNameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">系统设置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editSysNameForm">
                    <div class="mb-3">
                        <label class="form-label">系统名称</label>
                        <input type="text" class="form-control" id="sysNameInput" name="sys_name" value="<?php echo htmlentities((string) (isset($sysSettings['sys_name']) && ($sysSettings['sys_name'] !== '')?$sysSettings['sys_name']:'')); ?>" required>
                    </div>
                    <input type="hidden" name="field" value="webtitle">
                    <input type="hidden" name="__token__" value="<?php echo token(); ?>">
                    <input type="hidden" name="id" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitSysNameChange()">确认修改</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editMbNameModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">模板设置</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editMbNameForm">
                    <div class="mb-3">
                        <label class="form-label">模板路径</label>
                        <input type="text" class="form-control" id="mbNameInput" name="mbname" value="<?php echo htmlentities((string) (isset($sysSettings['mbname']) && ($sysSettings['mbname'] !== '')?$sysSettings['mbname']:'')); ?>" required>
                    </div>
                    <input type="hidden" name="field" value="mbname">
                    <input type="hidden" name="__token__" value="<?php echo token(); ?>">
                    <input type="hidden" name="id" value="1">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitMbNameChange()">确认修改</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">个人信息</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <div class="mb-3">
                        <label class="form-label">用户名</label>
                        <input type="text" class="form-control" name="uname" value="<?php echo htmlentities((string) (isset($user['uname']) && ($user['uname'] !== '')?$user['uname']:'')); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">昵称</label>
                        <input type="text" class="form-control" name="nname" value="<?php echo htmlentities((string) (isset($user['nname']) && ($user['nname'] !== '')?$user['nname']:'')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">手机号</label>
                        <input type="tel" class="form-control" name="uiphone" value="<?php echo htmlentities((string) (isset($user['uiphone']) && ($user['uiphone'] !== '')?$user['uiphone']:'')); ?>">
                    </div>
                    <input type="hidden" name="__token__" value="<?php echo token(); ?>">
                    <input type="hidden" name="uid" value="<?php echo htmlentities((string) $user['uid']); ?>">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="submitUserInfoChange()">保存修改</button>
            </div>
        </div>
    </div>
</div>
<script src="/template/default/js/jquery.min.js"></script>
<script src="/template/default/js/bootstrap.bundle.min.js"></script>
<script src="/template/default/js/bootstrap.min.js"></script>
<script src="/template/default/js/xt-toggle-switch.js"></script>
<script>
(function() {
    'use strict';
    window.showChangePasswordModal = function() {
        try {
            var modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
            modal.show();
            document.getElementById('changePasswordForm').reset();
        } catch (error) {
            console.error('显示修改密码模态框失败:', error);
            alert('无法打开修改密码窗口，请刷新页面后重试');
        }
    };
    window.showEditSysNameModal = function() {
        try {
            var modal = new bootstrap.Modal(document.getElementById('editSysNameModal'));
            modal.show();
        } catch (error) {
            console.error('显示系统设置模态框失败:', error);
            alert('无法打开系统设置窗口，请刷新页面后重试');
        }
    };
    window.showEditMbNameModal = function() {
        try {
            var modal = new bootstrap.Modal(document.getElementById('editMbNameModal'));
            modal.show();
        } catch (error) {
            console.error('显示模板设置模态框失败:', error);
            alert('无法打开模板设置窗口，请刷新页面后重试');
        }
    };
    window.showEditUserModal = function() {
        try {
            var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        } catch (error) {
            console.error('显示个人信息模态框失败:', error);
            alert('无法打开个人信息窗口，请刷新页面后重试');
        }
    };
    window.toggleRegState = function(isChecked) {
        var regStateValue = isChecked ? '0' : '1'; 
        var switchElement = document.getElementById('regSwitch');
        switchElement.disabled = true;
        var tempForm = document.createElement('form');
        tempForm.innerHTML = `
            <input type="hidden" name="field" value="regstate">
            <input type="hidden" name="regstate" value="${regStateValue}">
            <input type="hidden" name="__token__" value="${document.querySelector('input[name="__token__"]').value}">
            <input type="hidden" name="id" value="1">
        `;
        
        var formData = $(tempForm).serialize();
        
        $.ajax({
            url: '/index/wode/updateSystemSetting',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                switchElement.disabled = false;
                if (data.code === 1) {
                    alert('注册开关' + (isChecked ? '开启' : '关闭') + '成功');
                } else {
                    alert(data.msg || '操作失败');
                    switchElement.checked = !isChecked;
                }
            },
            error: function(xhr, status, error) {
                switchElement.disabled = false;
                console.error('注册开关请求失败:', error);
                alert('请求失败: ' + error);
                switchElement.checked = !isChecked;
            }
        });
    };
    window.submitUserInfoChange = function() {
        var form = $('#editUserForm');
        var formData = form.serialize();
        
        $.ajax({
            url: '/index/wode/doedituser',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.code === 1) {
                    alert(data.msg);
                    $('#editUserModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.msg || '修改失败');
                }
            },
            error: function(xhr, status, error) {
                console.error('个人信息修改请求失败:', error);
                alert('请求失败: ' + error);
            }
        });
    };
    function makeRequest(url, data, method, callback) {
        if (typeof jQuery !== 'undefined' && jQuery.ajax) {
            jQuery.ajax({
                url: url,
                type: method,
                data: data,
                dataType: 'json',
                success: callback,
                error: function(xhr, status, error) {
                    callback({code: 0, msg: '请求失败: ' + error});
                }
            });
        } else {
            var xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        callback(response);
                    } catch (e) {
                        callback({code: 0, msg: '解析响应失败'});
                    }
                }
            };
            
            xhr.onerror = function() {
                callback({code: 0, msg: '网络错误'});
            };
            var params = new URLSearchParams();
            if (data instanceof FormData) {
                for (var pair of data.entries()) {
                    params.append(pair[0], pair[1]);
                }
            } else if (typeof data === 'object') {
                for (var key in data) {
                    if (data.hasOwnProperty(key)) {
                        params.append(key, data[key]);
                    }
                }
            }
            
            xhr.send(params.toString());
        }
    }
    window.submitChangePassword = function() {
        var form = $('#changePasswordForm');
        var newPassword = $('#newPassword').val();
        var confirmPassword = $('#confirmPassword').val();
        
        if (newPassword !== confirmPassword) {
            alert('新密码和确认密码不一致！');
            return;
        }
        
        if (newPassword.length < 6) {
            alert('新密码长度不能少于6位！');
            return;
        }
        var formData = form.serialize();
        
        $.ajax({
            url: '/index/wode/changepassword',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.code === 1) {
                    alert(data.msg);
                    $('#changePasswordModal').modal('hide');
                    form[0].reset();
                } else {
                    alert(data.msg || '修改失败');
                }
            },
            error: function(xhr, status, error) {
                console.error('密码修改请求失败:', error);
                alert('请求失败: ' + error);
            }
        });
    };
    window.submitSysNameChange = function() {
        var sysName = document.getElementById('sysNameInput').value;
        
        if (!sysName) {
            alert('系统名称不能为空！');
            return;
        }
        var formData = $('#editSysNameForm').serialize();
        
        $.ajax({
            url: '/index/wode/updateSystemSetting',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.code === 1) {
                    alert(data.msg);
                    $('#editSysNameModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.msg || '修改失败');
                }
            },
            error: function(xhr, status, error) {
                console.error('系统设置请求失败:', error);
                alert('请求失败: ' + error);
            }
        });
    };
    window.submitMbNameChange = function() {
        var form = $('#editMbNameForm');
        var mbname = $('#mbNameInput').val();
        
        if (!mbname) {
            alert('模板路径不能为空！');
            return;
        }
        var formData = form.serialize();
        
        $.ajax({
            url: '/index/wode/updateSystemSetting',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(data) {
                if (data.code === 1) {
                    alert(data.msg);
                    $('#editMbNameModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.msg || '修改失败');
                }
            },
            error: function(xhr, status, error) {
                console.error('模板设置请求失败:', error);
                alert('请求失败: ' + error);
            }
        });
    };
    window.clearCache = function() {
        if (confirm('确定要清理缓存吗？')) {
            var token = document.querySelector('input[name="__token__"]');
            var tokenValue = token ? token.value : '';
            
            makeRequest('/index/wode/clearCache', {
                '__token__': tokenValue
            }, 'POST', function(data) {
                if (data.code === 1) {
                    alert(data.msg);
                } else {
                    alert(data.msg || '清理失败');
                }
            });
        }
    };
    window.logout = function() {
        if (confirm('确定要退出登录吗？')) {
            var token = document.querySelector('input[name="__token__"]');
            var tokenValue = token ? token.value : '';
            
            makeRequest('/index/wode/logout', {
                '__token__': tokenValue
            }, 'POST', function(data) {
                if (data.code === 1) {
                    localStorage.removeItem('auth_token');
                    sessionStorage.removeItem('user_session');
                    window.location.href = data.url || '/index/index/login';
                } else {
                    alert(data.msg || '退出登录失败');
                }
            });
        }
    };
    document.addEventListener('DOMContentLoaded', function() {
        var userInfoElement = document.querySelector('.wo-title');
        if (userInfoElement) {
            var onclickAttr = userInfoElement.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('uid=')) {
                var match = onclickAttr.match(/uid=([^&']+)/);
                if (match && match[1]) {
                    window.userId = match[1];
                }
            }
        }
        var touchElements = document.querySelectorAll('.sz-card-item, .xt-list-group-item');
        if (touchElements.forEach) {
            touchElements.forEach(function(element) {
                element.addEventListener('touchstart', function() {
                    this.style.opacity = '0.7';
                });
                element.addEventListener('touchend', function() {
                    this.style.opacity = '1';
                });
            });
        }
        if (typeof Promise === 'undefined') {
            console.warn('Promise not supported, loading polyfill');
        }
    });
    
})();
</script>
</body>
</html>
