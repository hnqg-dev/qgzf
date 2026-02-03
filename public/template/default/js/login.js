    // 动态计算底部区域高度
    function calculateBottomHeight() {
      const loginContainer = document.querySelector('.login01-login-container');
      const bottomArea = document.getElementById('bottomArea');
      
      if (loginContainer && bottomArea) {
        const viewportHeight = window.innerHeight;
        const loginRect = loginContainer.getBoundingClientRect();
        const distanceFromBottom = viewportHeight - loginRect.bottom;
        
    // 设置高度为div(1)底部到屏幕底部的距离
    bottomArea.style.height = distanceFromBottom + 'px';
    
    // 添加最小高度保护
    if (distanceFromBottom < 20) {
      bottomArea.style.height = '20px';
    }
        
        // 更新inner-content高度
        const innerContent = document.querySelector('.login01-inner-content');
        if (innerContent) {
          innerContent.style.height = '100%';
        }
      }
    }

    // 初始计算和窗口调整时重新计算
    window.addEventListener('load', calculateBottomHeight);
    window.addEventListener('resize', calculateBottomHeight);


    // 密码可见性切换
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // 切换图标 - 分别切换基础图标类和具体图标类
        const icon = this.querySelector('i');
        icon.classList.toggle('bi');
        icon.classList.toggle('bi-eye-slash');
        icon.classList.toggle('bi-eye');
    });
        
