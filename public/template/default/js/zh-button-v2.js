 // zho-swipe-actions.js 文件内容
        class ZhoSwipeActions {
            constructor(options) {
                this.container = document.querySelector(options.container);
                // 检查容器是否本身就是一个可滑动项
                if (this.container.classList.contains('zho-swipe-item')) {
                    this.items = [this.container];
                } else {
                    this.items = this.container.querySelectorAll(options.items);
                }
                this.currentOpenItem = null;
                this.itemStates = new Map();
                this.init();
            }

            init() {
                this.items.forEach(item => {
                    this.itemStates.set(item, {
                        isSwiped: false,
                        isScrolling: false
                    });
                    this.setupSwipe(item);
                });

                document.addEventListener('touchstart', this.handleGlobalTouch.bind(this));
            }

            setupSwipe(item) {
                let startX, startY, moveX, moveY;
                const itemState = this.itemStates.get(item);

                item.addEventListener('touchstart', (e) => {
                    if (e.touches.length > 1) return;

                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                    itemState.isScrolling = false;

                    e.stopPropagation();
                }, { passive: true });

                item.addEventListener('touchmove', (e) => {
                    if (e.touches.length > 1) return;

                    if (itemState.isSwiped) {
                        e.preventDefault();
                        return;
                    }

                    moveX = e.touches[0].clientX;
                    moveY = e.touches[0].clientY;

                    const diffX = moveX - startX;
                    const diffY = moveY - startY;

                    if (Math.abs(diffY) > Math.abs(diffX) && Math.abs(diffY) > 5) {
                        itemState.isScrolling = true;
                        return;
                    }

                    if (Math.abs(diffX) > 5) {
                        e.preventDefault();
                    }

                    if (diffX < 0) {
                        const actionsContainer = item.querySelector('.zho-swipe-actions');
                        const actionsWidth = actionsContainer.offsetWidth;
                        const translateX = Math.max(diffX, -actionsWidth);
                        item.querySelector('.zho-swipe-content').style.transform = `translateX(${translateX}px)`;
                    }
                }, { passive: false });

                item.addEventListener('touchend', (e) => {
                    if (itemState.isScrolling) {
                        itemState.isScrolling = false;
                        return;
                    }

                    const actionsContainer = item.querySelector('.zho-swipe-actions');
                    const actionsWidth = actionsContainer.offsetWidth;
                    const content = item.querySelector('.zho-swipe-content');
                    const currentTranslateX = parseInt(content.style.transform.replace('translateX(', '').replace('px)', '') || 0);

                    if (Math.abs(currentTranslateX) > actionsWidth * 0.4) {
                        content.style.transform = `translateX(-${actionsWidth}px)`;
                        this.closeOtherItems(item);
                        this.currentOpenItem = item;
                        itemState.isSwiped = true;
                    } else {
                        content.style.transform = 'translateX(0)';
                        if (this.currentOpenItem === item) {
                            this.currentOpenItem = null;
                        }
                        itemState.isSwiped = false;
                    }
                }, { passive: true });

                item.addEventListener('touchcancel', (e) => {
                    const content = item.querySelector('.zho-swipe-content');
                    content.style.transform = 'translateX(0)';
                    if (this.currentOpenItem === item) {
                        this.currentOpenItem = null;
                    }
                    itemState.isSwiped = false;
                    itemState.isScrolling = false;
                }, { passive: true });
            }

            closeOtherItems(currentItem) {
                this.items.forEach(item => {
                    if (item !== currentItem) {
                        const itemState = this.itemStates.get(item);
                        if (item.querySelector('.zho-swipe-content').style.transform !== 'translateX(0px)') {
                            item.querySelector('.zho-swipe-content').style.transform = 'translateX(0)';
                            itemState.isSwiped = false;
                        }
                    }
                });
            }

            handleGlobalTouch(e) {
                if (this.currentOpenItem && !this.currentOpenItem.contains(e.target)) {
                    this.currentOpenItem.querySelector('.zho-swipe-content').style.transform = 'translateX(0)';

                    const itemState = this.itemStates.get(this.currentOpenItem);
                    itemState.isSwiped = false;

                    this.currentOpenItem = null;
                }
            }
        }
