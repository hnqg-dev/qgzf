// 创建样式
const style = document.createElement('style');
style.textContent = `
.refresh-indicator {
    position: fixed;
    top: -50px;
    left: 0;
    width: 100%;
    height: 50px;
    text-align: center;
    line-height: 50px;
    transition: top 0.3s;
    background: #f5f5f5;
    z-index: 9999;
}
`;
document.head.appendChild(style);

// 创建容器
const pullToRefreshContainer = (function() {
    const container = document.createElement('div');
    container.className = 'refresh-indicator';
    container.textContent = '松开刷新...';
    document.body.insertBefore(container, document.body.firstChild);
    return container;
})();

const PullToRefresh = (function() {
    let startY = 0;
    let currentY = 0;
    let loading = false;
    let callbacks = [];

    const indicator = document.querySelector('.refresh-indicator');

    function handleTouchStart(e) {
        if (loading || window.scrollY > 0) return;

        startY = e.touches[0].pageY;
        currentY = startY;

        document.addEventListener('touchmove', handleTouchMove, {passive: false});
        document.addEventListener('touchend', handleTouchEnd);
    }

    function handleTouchMove(e) {
        if (loading) return;

        currentY = e.touches[0].pageY;
        const diff = currentY - startY;

        if (diff > 0) {
            e.preventDefault();
            updateIndicator(diff);
        }
    }

    function handleTouchEnd() {
        document.removeEventListener('touchmove', handleTouchMove);
        document.removeEventListener('touchend', handleTouchEnd);

        if (currentY - startY > 100) {
            triggerRefresh();
        } else {
            resetIndicator();
        }
    }

    function updateIndicator(diff) {
        indicator.style.top = `${Math.min(50, diff - 50)}px`;
    }

    function resetIndicator() {
        indicator.style.top = '-50px';
    }

    async function triggerRefresh() {
        loading = true;
        indicator.style.top = '0';
        indicator.textContent = '正在刷新...';

        try {
            await Promise.all(callbacks.map(cb => cb()));
        } finally {
            setTimeout(() => {
                resetIndicator();
                loading = false;
            }, 500);
        }
    }

    return {
        init: function() {
            document.addEventListener('touchstart', handleTouchStart, {passive: true});
        },
        addCallback: function(callback) {
            if (typeof callback === 'function') {
                callbacks.push(callback);
            }
        },
        destroy: function() {
            document.removeEventListener('touchstart', handleTouchStart);
            document.removeEventListener('touchmove', handleTouchMove);
            document.removeEventListener('touchend', handleTouchEnd);
        }
    };
})();

// 自动初始化
PullToRefresh.init();

// 导出接口
window.PullToRefresh = PullToRefresh;