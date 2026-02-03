        /* zhhx- 核心JavaScript代码 */
        class zhhxSwipeButton {
            constructor(element) {
                this.element = element;
                this.isOpen = false;
                this.startX = 0;
                this.currentX = 0;
                this.threshold = 50;
                this.buttonsWidth = 0;
                
                if (!this.element) {
                    console.error("Element not found.");
                    return;
                }
                
                this.init();
            }
            
            init() {
                // 获取按钮容器
                this.buttonsContainer = this.element.querySelector('.zhhx-swipe-buttons-container');
                if (!this.buttonsContainer) {
                    console.error("Buttons container not found.");
                    return;
                }
                
                // 计算按钮总宽度
                const buttons = this.buttonsContainer.querySelectorAll('.zhhx-swipe-button');
                if (buttons.length > 0) {
                    this.buttonsWidth = buttons.length * 62 + 30; // 按钮宽度+间距
                }
                
                // 添加触摸事件监听
                this.content = this.element.querySelector('.zhhx-swipe-content');
                this.content.addEventListener('touchstart', this.onTouchStart.bind(this));
                this.content.addEventListener('touchmove', this.onTouchMove.bind(this));
                this.content.addEventListener('touchend', this.onTouchEnd.bind(this));
                
                // 添加到全局实例数组
                if (!window.zhhxSwipeInstances) {
                    window.zhhxSwipeInstances = [];
                }
                window.zhhxSwipeInstances.push(this);
            }
            
            onTouchStart(e) {
                e.stopPropagation();
                
                // 记录起始位置和时间
                this.startX = e.touches[0].clientX;
                this.startY = e.touches[0].clientY;
                this.startTime = Date.now();
                this.isSwiping = false;
            }
            
            onTouchMove(e) {
                if (this.isOpen) {
                    e.stopPropagation();
                }
                
                this.currentX = e.touches[0].clientX;
                this.currentY = e.touches[0].clientY;
                const diffX = this.startX - this.currentX;
                const diffY = Math.abs(this.startY - this.currentY);
                
                // 判断是否为真实左滑：水平移动距离大于垂直移动距离的2倍，且水平移动距离大于10px
                if (diffX > diffY * 2 && diffX > 10) {
                    this.isSwiping = true;
                    e.preventDefault();
                }
                
                // 只有确定为滑动时才进行位移
                if (this.isSwiping) {
                    if (diffX > 0 && diffX <= this.buttonsWidth) {
                        this.content.style.transform = `translateX(-${diffX}px)`;
                    } else if (diffX > this.buttonsWidth) {
                        this.content.style.transform = `translateX(-${this.buttonsWidth}px)`;
                    }
                }
            }
            
            onTouchEnd(e) {
                const diffX = this.startX - this.currentX;
                const duration = Date.now() - this.startTime;
                
                // 只有确定为滑动动作且移动距离超过阈值才触发左滑
                if (this.isSwiping && diffX > this.threshold) {
                    this.open();
                } else {
                    this.close();
                }
                
                this.isSwiping = false;
            }
            
            open() {
                this.content.style.transform = `translateX(-${this.buttonsWidth}px)`;
                this.element.classList.add('active');
                this.isOpen = true;
            }
            
            close() {
                this.content.style.transform = 'translateX(0)';
                this.element.classList.remove('active');
                this.isOpen = false;
            }
        }
        
        // 自动初始化所有左滑项目
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.zhhx-swipe-item').forEach(element => {
                new zhhxSwipeButton(element);
            });
        });
