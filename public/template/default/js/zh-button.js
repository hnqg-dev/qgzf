
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
            item.addEventListener('touchstart', this.handleTouchStart, { passive: true });
            
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
        HdSwipeList.startY = e.touches ? e.touches[0].clientY : e.clientY;
        HdSwipeList.currentItem = e.currentTarget;
        HdSwipeList.isSwiping = false;
        HdSwipeList.isHorizontal = false;
        
        // 重置所有其他项目的状态
        if (HdSwipeList.currentItem.style.transform !== 'translateX(0px)') {
            const allItems = document.querySelectorAll('.hd-swipe-item');
            allItems.forEach(otherItem => {
                if (otherItem !== HdSwipeList.currentItem && otherItem.style.transform !== 'translateX(0px)') {
                    otherItem.style.transform = 'translateX(0)';
                }
            });
        }
    },
    
    // 触摸移动处理
    handleTouchMove: function(e) {
        if (!HdSwipeList.startX) return;
        
        HdSwipeList.currentX = e.touches ? e.touches[0].clientX : e.clientX;
        HdSwipeList.currentY = e.touches ? e.touches[0].clientY : e.clientY;
        
        const diffX = HdSwipeList.startX - HdSwipeList.currentX;
        const diffY = HdSwipeList.startY - HdSwipeList.currentY;
        
        // 判断是水平滑动还是垂直滑动
        if (!HdSwipeList.isHorizontal && Math.abs(diffX) > 5 && Math.abs(diffX) > Math.abs(diffY)) {
            HdSwipeList.isHorizontal = true;
            HdSwipeList.isSwiping = true;
        }
        
        // 只有水平滑动才处理
        if (HdSwipeList.isHorizontal) {
            // 只有向左滑动才有效
            if (diffX > 0) {
                // 获取操作区域宽度
                const wrapper = HdSwipeList.currentItem.closest('.hd-swipe-item-wrapper');
                const actions = wrapper.querySelector('.hd-swipe-actions');
                const actionWidth = actions.offsetWidth;
                
                // 限制最大滑动距离
                const translateX = Math.min(diffX, actionWidth);
                HdSwipeList.currentItem.style.transform = `translateX(-${translateX}px)`;
                
                // 阻止默认行为防止页面滚动
                if (e.cancelable) {
                    e.preventDefault();
                }
            } else if (diffX < 0) {
                // 向右滑动，恢复原状
                HdSwipeList.currentItem.style.transform = 'translateX(0)';
            }
        }
    },
    
    // 触摸结束处理
    handleTouchEnd: function() {
        if (!HdSwipeList.isHorizontal || !HdSwipeList.currentItem) {
            HdSwipeList.reset();
            return;
        }
        
        // 获取操作区域宽度
        const wrapper = HdSwipeList.currentItem.closest('.hd-swipe-item-wrapper');
        const actions = wrapper.querySelector('.hd-swipe-actions');
        const actionWidth = actions.offsetWidth;
        
        const diffX = HdSwipeList.startX - HdSwipeList.currentX;
        
        if (diffX > 60) {
            // 滑动足够远，显示操作按钮
            HdSwipeList.currentItem.style.transform = `translateX(-${actionWidth}px)`;
            HdSwipeList.currentItem.setAttribute('data-swiped', 'true');
        } else {
            // 滑动不足，恢复原状
            HdSwipeList.currentItem.style.transform = 'translateX(0)';
            HdSwipeList.currentItem.removeAttribute('data-swiped');
        }
        
        HdSwipeList.reset();
    },
    
    // 重置状态
    reset: function() {
        HdSwipeList.startX = null;
        HdSwipeList.startY = null;
        HdSwipeList.currentX = null;
        HdSwipeList.currentY = null;
        HdSwipeList.isSwiping = false;
        HdSwipeList.isHorizontal = false;
    },
    
    // 项目点击处理
    handleItemClick: function(e) {
        if (!e.target.classList.contains('hd-swipe-btn') && 
            !e.target.parentElement.classList.contains('hd-swipe-btn')) {
            // 点击项目内容区域，恢复原状
            this.style.transform = 'translateX(0)';
            this.removeAttribute('data-swiped');
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
        
        // 注意：点击按钮后不关闭功能区
    },
    
    // 文档触摸处理
    handleDocumentTouch: function(e) {
        // 检查是否点击了滑动项目或按钮
        const isSwipeItem = e.target.closest('.hd-swipe-item');
        const isSwipeButton = e.target.closest('.hd-swipe-btn');
        
        if (!isSwipeItem && !isSwipeButton) {
            // 点击了页面其他区域，收起所有操作按钮
            const allItems = document.querySelectorAll('.hd-swipe-item');
            allItems.forEach(item => {
                item.style.transform = 'translateX(0)';
                item.removeAttribute('data-swiped');
            });
        }
    },
    
    // 状态变量
    startX: null,
    startY: null,
    currentX: null,
    currentY: null,
    isSwiping: false,
    isHorizontal: false,
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
        }
    });
});