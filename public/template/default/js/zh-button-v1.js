
// 滑动列表组件 - 全局封装
window.HdSwipeList = {
    // 初始化所有带属性的滑动列表
    initAll: function() {
        document.querySelectorAll('[data-hd-swipe-enabled]').forEach(container => {
            this.init(container.id);
        });
    },
    
    // 初始化单个滑动列表
    init: function(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        const items = container.querySelectorAll('.hd-swipe-item');
        
        // 为每个项目添加触摸事件
        items.forEach(item => {
            // 触摸开始
            item.addEventListener('touchstart', this.handleTouchStart, { passive: false });
            
            // 触摸移动
            item.addEventListener('touchmove', this.handleTouchMove, { passive: false });
            
            // 触摸结束
            item.addEventListener('touchend', this.handleTouchEnd, { passive: true });
            
            // 点击项目其他区域恢复原状
            item.addEventListener('click', this.handleItemClick);
        });
        
        // 按钮点击事件
        const buttons = container.querySelectorAll('.hd-swipe-btn');
        buttons.forEach(button => {
            button.addEventListener('click', this.handleButtonClick);
            button.addEventListener('touchend', this.handleButtonClick);
        });
        
        // 点击页面其他区域收起操作按钮
        document.addEventListener('touchstart', this.handleDocumentTouch);
        document.addEventListener('mousedown', this.handleDocumentTouch);
    },
    
    // 触摸开始处理
    handleTouchStart: function(e) {
        // 防止多点触摸
        if (e.touches && e.touches.length > 1) return;
        
        HdSwipeList.startX = e.touches ? e.touches[0].clientX : e.clientX;
        HdSwipeList.currentItem = e.currentTarget;
        HdSwipeList.isSwiping = true;
        
        // 重置所有其他项目的状态
        const allItems = document.querySelectorAll('.hd-swipe-item');
        allItems.forEach(otherItem => {
            if (otherItem !== HdSwipeList.currentItem && otherItem.style.transform !== 'translateX(0px)') {
                otherItem.style.transform = 'translateX(0)';
            }
        });
        
        // 阻止默认行为防止页面滚动
        if (e.cancelable) {
            e.preventDefault();
        }
    },
    
    // 触摸移动处理
    handleTouchMove: function(e) {
        if (!HdSwipeList.isSwiping) return;
        
        HdSwipeList.currentX = e.touches ? e.touches[0].clientX : e.clientX;
        HdSwipeList.diffX = HdSwipeList.startX - HdSwipeList.currentX;
        
        // 只有向左滑动才有效
        if (HdSwipeList.diffX > 0) {
            // 获取操作区域宽度
            const wrapper = HdSwipeList.currentItem.closest('.hd-swipe-item-wrapper');
            const actions = wrapper.querySelector('.hd-swipe-actions');
            const actionWidth = actions.offsetWidth;
            
            // 限制最大滑动距离
            const translateX = Math.min(HdSwipeList.diffX, actionWidth);
            HdSwipeList.currentItem.style.transform = `translateX(-${translateX}px)`;
            
            // 阻止默认行为防止页面滚动
            if (e.cancelable && Math.abs(HdSwipeList.diffX) > 10) {
                e.preventDefault();
            }
        } else if (HdSwipeList.diffX < 0) {
            // 向右滑动，恢复原状
            HdSwipeList.currentItem.style.transform = 'translateX(0)';
        }
    },
    
    // 触摸结束处理
    handleTouchEnd: function() {
        HdSwipeList.isSwiping = false;
        
        if (!HdSwipeList.currentItem) return;
        
        // 获取操作区域宽度
        const wrapper = HdSwipeList.currentItem.closest('.hd-swipe-item-wrapper');
        const actions = wrapper.querySelector('.hd-swipe-actions');
        const actionWidth = actions.offsetWidth;
        
        if (HdSwipeList.diffX > 60) {
            // 滑动足够远，显示操作按钮
            HdSwipeList.currentItem.style.transform = `translateX(-${actionWidth}px)`;
        } else {
            // 滑动不足，恢复原状
            HdSwipeList.currentItem.style.transform = 'translateX(0)';
        }
        
        // 重置变量
        HdSwipeList.startX = null;
        HdSwipeList.currentX = null;
        HdSwipeList.diffX = null;
    },
    
    // 项目点击处理
    handleItemClick: function(e) {
        if (!e.target.classList.contains('hd-swipe-btn') && 
            !e.target.parentElement.classList.contains('hd-swipe-btn')) {
            e.currentTarget.style.transform = 'translateX(0)';
        }
    },
    
    // 按钮点击处理
    handleButtonClick: function(e) {
        e.stopPropagation();
        const action = e.currentTarget.getAttribute('data-action');
        const wrapper = e.currentTarget.closest('.hd-swipe-item-wrapper');
        
        // 触发自定义事件
        const event = new CustomEvent('hdSwipeAction', {
            detail: {
                action: action,
                element: wrapper
            },
            bubbles: true
        });
        
        wrapper.dispatchEvent(event);
        
        // 防止触发底层点击事件
        if (e.cancelable) {
            e.preventDefault();
        }
    },
    
    // 文档触摸处理
    handleDocumentTouch: function(e) {
        if (HdSwipeList.currentItem && !HdSwipeList.currentItem.contains(e.target)) {
            HdSwipeList.currentItem.style.transform = 'translateX(0)';
        }
    },
    
    // 状态变量
    startX: null,
    currentX: null,
    diffX: null,
    isSwiping: false,
    currentItem: null
};

// 自动初始化所有滑动列表
document.addEventListener('DOMContentLoaded', function() {
    HdSwipeList.initAll();
    
    // 监听演示列表的滑动操作事件
    document.getElementById('swipe-enabled-list').addEventListener('hdSwipeAction', function(e) {
        const action = e.detail.action;
        const item = e.detail.element;
        const title = item.querySelector('strong').textContent;
        
        switch(action) {
            case 'delete':
                if (confirm('确定要删除"' + title + '"吗？')) {
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                    }, 300);
                }
                break;
            case 'detail':
                alert('显示"' + title + '"的详情');
                break;
            case 'edit':
                alert('编辑"' + title + '"');
                break;
            case 'share':
                alert('分享"' + title + '"');
                break;
        }
    });
});