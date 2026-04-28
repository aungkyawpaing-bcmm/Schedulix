import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('sidebarShell', () => ({
    collapsed: false,

    init() {
        this.collapsed = window.localStorage.getItem('schedulix-sidebar-collapsed') === 'true';
    },

    toggleSidebar() {
        this.collapsed = ! this.collapsed;
        window.localStorage.setItem('schedulix-sidebar-collapsed', this.collapsed ? 'true' : 'false');
    },
}));

Alpine.data('scheduleTableScroller', () => ({
    spacerWidth: 0,
    syncing: false,
    resizeHandler: null,

    init() {
        this.refreshWidth();
        requestAnimationFrame(() => this.refreshWidth());
        window.setTimeout(() => this.refreshWidth(), 150);

        this.resizeHandler = () => this.refreshWidth();
        window.addEventListener('resize', this.resizeHandler);
    },

    refreshWidth() {
        this.$nextTick(() => {
            this.spacerWidth = Math.max(
                this.$refs.detailTable?.scrollWidth ?? 0,
                this.$refs.mainScroll?.scrollWidth ?? 0,
            );
        });
    },

    syncFromMain() {
        if (this.syncing || ! this.$refs.bottomScroll || ! this.$refs.mainScroll) {
            return;
        }

        this.syncing = true;
        this.$refs.bottomScroll.scrollLeft = this.$refs.mainScroll.scrollLeft;

        requestAnimationFrame(() => {
            this.syncing = false;
        });
    },

    syncFromBottom() {
        if (this.syncing || ! this.$refs.bottomScroll || ! this.$refs.mainScroll) {
            return;
        }

        this.syncing = true;
        this.$refs.mainScroll.scrollLeft = this.$refs.bottomScroll.scrollLeft;

        requestAnimationFrame(() => {
            this.syncing = false;
        });
    },

    destroy() {
        if (this.resizeHandler) {
            window.removeEventListener('resize', this.resizeHandler);
        }
    },
}));

Alpine.start();
