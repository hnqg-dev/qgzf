
// xt-toggle-switch.js - 兼容Android、华为和iOS的开关组件
(function() {
    'use strict';
    
    // 检测设备类型
    function detectDevice() {
        const ua = navigator.userAgent;
        
        if (/android/i.test(ua)) {
            return 'android';
        } else if (/huawei/i.test(ua)) {
            return 'huawei';
        } else if (/ipad|iphone|ipod/i.test(ua)) {
            return 'ios';
        } else {
            return 'other';
        }
    }
    
    // 初始化开关
    function initToggleSwitch(switchId, callback) {
        const toggleSwitch = document.getElementById(switchId);
        if (!toggleSwitch) return;
        
        const deviceType = detectDevice();
        
        // 添加设备特定的类
        toggleSwitch.parentElement.classList.add('xt-switch-' + deviceType);
        
        // 添加事件监听
        toggleSwitch.addEventListener('change', function() {
            const isChecked = this.checked;
            
            if (callback && typeof callback === 'function') {
                callback(isChecked, deviceType);
            }
            
            // 触发自定义事件
            const event = new CustomEvent('xtToggleChange', {
                detail: {
                    checked: isChecked,
                    device: deviceType,
                    id: switchId
                }
            });
            document.dispatchEvent(event);
        });
        
        // 针对不同设备的特殊处理
        switch(deviceType) {
            case 'ios':
                // iOS设备添加平滑过渡
                toggleSwitch.style.transition = 'all 0.3s ease';
                break;
            case 'android':
                // Android设备添加涟漪效果
                toggleSwitch.addEventListener('mousedown', function() {
                    this.parentElement.classList.add('xt-switch-active');
                });
                toggleSwitch.addEventListener('mouseup', function() {
                    this.parentElement.classList.remove('xt-switch-active');
                });
                toggleSwitch.addEventListener('touchstart', function() {
                    this.parentElement.classList.add('xt-switch-active');
                });
                toggleSwitch.addEventListener('touchend', function() {
                    this.parentElement.classList.remove('xt-switch-active');
                });
                break;
            case 'huawei':
                // 华为设备特殊处理
                toggleSwitch.addEventListener('change', function() {
                    if (this.checked) {
                        this.parentElement.style.boxShadow = '0 0 10px rgba(17, 102, 187, 0.5)';
                    } else {
                        this.parentElement.style.boxShadow = 'none';
                    }
                });
                break;
        }
        
        // 返回开关状态
        return {
            isChecked: toggleSwitch.checked,
            device: deviceType,
            set: function(checked) {
                toggleSwitch.checked = checked;
                // 触发change事件
                const event = new Event('change');
                toggleSwitch.dispatchEvent(event);
            },
            toggle: function() {
                this.set(!toggleSwitch.checked);
            }
        };
    }
    
    // 暴露到全局作用域
    window.xtToggleSwitch = {
        init: initToggleSwitch,
        detectDevice: detectDevice
    };
})();

// 使用示例
document.addEventListener('DOMContentLoaded', function() {
    // 初始化开关
    const registerSwitch = window.xtToggleSwitch.init('xt-register-toggle', function(isChecked, deviceType) {
        console.log('注册开关状态: ' + (isChecked ? '开启' : '关闭'));
        console.log('设备类型: ' + deviceType);
        
        // 更新状态显示
        document.getElementById('xt-toggle-status').textContent = isChecked ? '开启' : '关闭';
    });
    
    // 也可以监听自定义事件
    document.addEventListener('xtToggleChange', function(e) {
        console.log('开关变化事件:', e.detail);
    });
});
