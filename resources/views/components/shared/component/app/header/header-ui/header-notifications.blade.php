<div class="relative" x-data="{
    notiOpen: false,
    allNotiOpen: false,
    notifications: [],
    allNotifications: [],
    unreadCount: {{ auth()->check() ? app(App\Services\NotificationService::class)->getUnreadCountForUser(auth()->user()) : 0 }},
    loaded: false,
    loading: false,
    panelLoading: false,
    notiPage: 1,
    hasMore: true,

    toggleOpen() {
        this.notiOpen = !this.notiOpen;
        if (this.notiOpen && !this.loaded) {
            this.fetchDropdownNotifications();
        }
    },

    // Gọi API lấy 5 thông báo mới nhất cho Dropdown
    async fetchDropdownNotifications() {
        if (this.loading) return;
        this.loading = true;
        try {
            const response = await fetch('/api/v1/notifications?per_page=5', {
                headers: {
                    'Accept': 'application/json',
                }
            });
            const res = await response.json();
            if (res.success) {
                this.notifications = res.data;
                this.unreadCount = res.unread_count;
                this.loaded = true;
            }
        } catch (e) {
            console.error('Lỗi khi tải thông báo dropdown:', e);
        } finally {
            this.loading = false;
        }
    },

    // Gọi API lấy thông báo cho Panel (hỗ trợ phân trang cuộn)
    async fetchPanelNotifications() {
        if (!this.hasMore || this.panelLoading) return;
        this.panelLoading = true;
        try {
            const response = await fetch(`/api/v1/notifications?per_page=10&page=${this.notiPage}`, {
                headers: {
                    'Accept': 'application/json',
                }
            });
            const res = await response.json();
            if (res.success) {
                this.allNotifications = [...this.allNotifications, ...res.data];
                this.unreadCount = res.unread_count;
                
                if (res.current_page >= res.last_page) {
                    this.hasMore = false;
                } else {
                    this.notiPage++;
                }
            }
        } catch (e) {
            console.error('Lỗi khi tải thông báo panel:', e);
        } finally {
            this.panelLoading = false;
        }
    },

    // Đánh dấu một thông báo là đã đọc
    async markAsRead(id) {
        try {
            const response = await fetch(`/api/v1/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const res = await response.json();
            if (res.success) {
                this.unreadCount = res.unread_count;
                
                // Cập nhật client-side state
                const updateStatus = noti => noti.id === id ? { ...noti, is_read: true } : noti;
                this.notifications = this.notifications.map(updateStatus);
                this.allNotifications = this.allNotifications.map(updateStatus);
            }
        } catch (e) {
            console.error('Lỗi khi đánh dấu đã đọc:', e);
        }
    },

    // Đánh dấu tất cả thông báo là đã đọc
    async markAllAsRead() {
        try {
            const response = await fetch('/api/v1/notifications/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const res = await response.json();
            if (res.success) {
                this.unreadCount = 0;
                
                // Cập nhật client-side state
                const makeRead = noti => ({ ...noti, is_read: true });
                this.notifications = this.notifications.map(makeRead);
                this.allNotifications = this.allNotifications.map(makeRead);
            }
        } catch (e) {
            console.error('Lỗi khi đánh dấu tất cả đã đọc:', e);
        }
    },

    // Xóa tất cả thông báo của chính user
    async clearAll() {
        if (!confirm('{{ __("Are you sure you want to clear all notifications?") }}')) return;
        try {
            const response = await fetch('/api/v1/notifications/clear-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });
            const res = await response.json();
            if (res.success) {
                this.unreadCount = 0;
                this.notifications = [];
                this.allNotifications = [];
                this.hasMore = false;
            }
        } catch (e) {
            console.error('Lỗi khi xóa tất cả thông báo:', e);
        }
    },

    // Mở panel toàn bộ thông báo, đóng dropdown nhỏ
    openAllNotifications() {
        this.notiOpen = false;
        this.allNotiOpen = true;
        document.body.style.overflow = 'hidden';
        
        // Reset panel state trước khi load
        this.allNotifications = [];
        this.notiPage = 1;
        this.hasMore = true;
        this.fetchPanelNotifications();
    },

    // Đóng panel toàn bộ thông báo
    closeAllNotifications() {
        this.allNotiOpen = false;
        document.body.style.overflow = '';
        
        // Khi đóng panel, ta reload lại dropdown để đồng bộ dữ liệu mới nhất
        this.fetchDropdownNotifications();
    },

    notiIcon(type) {
        const map = {
            order_cancelled: 'cancel',
            order_completed: 'check_circle',
            order_refund: 'currency_exchange',
            success: 'check_circle',
            warning: 'warning',
            info: 'info',
        };
        return map[type] || 'notifications';
    },

    notiColor(type) {
        const map = {
            order_cancelled: 'text-rose-500 bg-rose-50 dark:bg-rose-500/10',
            order_completed: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10',
            order_refund: 'text-blue-500 bg-blue-50 dark:bg-blue-500/10',
            success: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10',
            warning: 'text-amber-500 bg-amber-50 dark:bg-amber-500/10',
        };
        return map[type] || 'text-primary bg-primary/10';
    }
}">
    <button @click="toggleOpen()"
        class="relative flex size-8 sm:size-10 cursor-pointer items-center justify-center rounded-full text-slate-900 dark:text-white transition-colors"
        :class="notiOpen ? 'bg-primary text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-800'">
        <span class="material-symbols-outlined text-[20px] sm:text-[24px]">notifications</span>
        {{-- Badge chưa đọc --}}
        <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount" x-cloak
            class="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white px-1"></span>
    </button>

    {{-- Backdrop trên mobile để đóng dropdown --}}
    <div x-show="notiOpen" @click="notiOpen = false" x-cloak class="sm:hidden fixed inset-0 z-[199]"></div>

    {{-- Dropdown thông báo --}}
    {{-- Mobile: fixed căn giữa màn hình, Desktop: absolute gắn vào button --}}
    <div x-show="notiOpen" @click.outside="notiOpen = false" @keydown.escape.window="notiOpen = false"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-[56px] right-3 w-[calc(100vw-24px)] max-w-[380px]
               sm:absolute sm:top-[100%] sm:left-auto sm:right-0 sm:translate-x-0 sm:mt-3 sm:w-[380px]
               bg-app-surface rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] border border-app-border z-[200] origin-top" x-cloak>

        {{-- Mũi tên trỏ vào icon (tự động tính vị trí) --}}
        <div x-ref="arrowOuter" x-effect="
                if(notiOpen) {
                    $nextTick(() => {
                        let btn = $root.querySelector('button');
                        let btnCenter = btn.getBoundingClientRect().left + btn.offsetWidth / 2;
                        let dropLeft = $el.parentElement.getBoundingClientRect().left;
                        $refs.arrowOuter.style.left = (btnCenter - dropLeft - 9) + 'px';
                        $refs.arrowInner.style.left = (btnCenter - dropLeft - 7) + 'px';
                    })
                }
            "
            class="absolute -top-[9px] w-0 h-0 border-l-[9px] border-l-transparent border-r-[9px] border-r-transparent border-b-[9px] border-b-app-border">
        </div>
        <div x-ref="arrowInner"
            class="absolute -top-[7px] w-0 h-0 border-l-[7px] border-l-transparent border-r-[7px] border-r-transparent border-b-[7px] border-b-app-surface">
        </div>

        {{-- Header --}}
        <div
            class="px-4 py-3 border-b border-app-border flex items-center justify-between rounded-t-xl bg-inherit relative z-10">
            <h3 class="font-bold text-app-text text-[15px]">{{ __('Notifications') }}</h3>
            <div class="flex items-center gap-3">
                <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-xs text-primary hover:text-primary-hover font-semibold transition-colors">{{ __('Mark all as read') }}</button>
                <button @click="openAllNotifications()" class="text-xs text-app-muted hover:text-app-text transition-colors">{{ __('See all') }}</button>
            </div>
        </div>

        {{-- Spinner loading --}}
        <div x-show="loading" class="flex items-center justify-center py-8">
            <div class="size-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
        </div>

        {{-- Danh sách thông báo --}}
        <div x-show="!loading && notifications.length > 0" class="max-h-[60vh] sm:max-h-[400px] overflow-y-auto scrollbar-hide">
            <template x-for="noti in notifications" :key="noti.id">
                <a :href="noti.action_url || '#'" @click="markAsRead(noti.id); if (!noti.action_url) $event.preventDefault(); notiOpen = false;"
                    class="flex gap-3 px-4 py-3 border-b border-app-border hover:bg-app-surface transition-colors"
                    :class="!noti.is_read ? 'bg-primary/[0.03] dark:bg-primary/[0.05]' : ''">
                    {{-- Icon --}}
                    <div class="shrink-0 w-9 h-9 rounded-full flex items-center justify-center"
                        :class="notiColor(noti.type)">
                        <span class="material-symbols-outlined text-[18px]" x-text="notiIcon(noti.type)"></span>
                    </div>
                    {{-- Content --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-app-text leading-snug"
                            :class="!noti.is_read ? 'font-bold' : 'font-medium'" x-text="noti.title"></p>
                        <p class="text-xs text-app-muted mt-0.5 leading-relaxed line-clamp-2" x-text="noti.message"></p>
                        <p class="text-[11px] text-app-muted opacity-80 mt-1" x-text="noti.created_at_human"></p>
                    </div>
                </a>
            </template>
        </div>

        {{-- Rỗng --}}
        <div x-show="!loading && notifications.length === 0"
            class="flex flex-col items-center justify-center py-8 px-4 text-app-muted">
            <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">notifications_off</span>
            <p class="text-sm font-medium">{{ __('You do not have any new notifications!') }}</p>
        </div>
    </div>

    {{-- ======================================================================== --}}
    {{-- PANEL TOÀN MÀN HÌNH "XEM TẤT CẢ" — Slide-in từ phải, có nút X đóng    --}}
    {{-- ======================================================================== --}}

    {{-- Backdrop mờ --}}
    <div x-show="allNotiOpen" x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeAllNotifications()"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[300]">
    </div>

    {{-- Panel slide-in --}}
    <div x-show="allNotiOpen" x-cloak
        @keydown.escape.window="closeAllNotifications()"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-[80vw] sm:w-full max-w-md bg-app-surface border-l border-app-border shadow-2xl z-[301] flex flex-col">

        {{-- Header cố định --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-app-border bg-app-surface shrink-0">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[22px] text-primary">notifications</span>
                <h2 class="text-base font-bold text-app-text">{{ __('All Notifications') }}</h2>
                <span x-show="unreadCount > 0" x-cloak
                    class="inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-full bg-rose-500 text-[11px] font-bold text-white"
                    x-text="unreadCount"></span>
            </div>
            <button @click="closeAllNotifications()"
                class="flex items-center justify-center size-9 rounded-lg hover:bg-app-surface/80 text-app-muted hover:text-app-text transition-colors"
                aria-label="{{ __('Close') }}">
                <span class="material-symbols-outlined text-[22px]">close</span>
            </button>
        </div>

        {{-- Toolbar hành động --}}
        <div class="px-5 py-2.5 border-b border-app-border bg-slate-50/50 dark:bg-slate-800/10 shrink-0 flex items-center justify-between">
            <button @click="markAllAsRead()" 
                class="text-xs font-semibold text-primary hover:text-primary-hover flex items-center gap-1 transition-colors"
                x-show="unreadCount > 0">
                <span class="material-symbols-outlined text-[16px]">done_all</span>
                {{ __('Mark all as read') }}
            </button>
            <div x-show="unreadCount === 0"></div>
            <button @click="clearAll()" 
                class="text-xs font-semibold text-rose-500 hover:text-rose-600 flex items-center gap-1 transition-colors">
                <span class="material-symbols-outlined text-[16px]">delete</span>
                {{ __('Clear all') }}
            </button>
        </div>

        {{-- Danh sách thông báo — scroll toàn bộ (Infinite Scroll) --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar flex flex-col"
            @scroll="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 20) { fetchPanelNotifications(); }">
            
            {{-- Có thông báo --}}
            <div x-show="allNotifications.length > 0" class="flex-1">
                <template x-for="noti in allNotifications" :key="'all-' + noti.id">
                    <a :href="noti.action_url || '#'" @click="markAsRead(noti.id); if (!noti.action_url) $event.preventDefault(); closeAllNotifications();"
                        class="flex gap-3 px-5 py-4 border-b border-app-border hover:bg-primary/[0.03] dark:hover:bg-primary/[0.05] transition-colors"
                        :class="!noti.is_read ? 'bg-primary/[0.03] dark:bg-primary/[0.05]' : ''">
                        {{-- Icon --}}
                        <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                            :class="notiColor(noti.type)">
                            <span class="material-symbols-outlined text-[20px]" x-text="notiIcon(noti.type)"></span>
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-app-text leading-snug"
                                :class="!noti.is_read ? 'font-bold' : 'font-medium'" x-text="noti.title"></p>
                            <p class="text-xs text-app-muted mt-1 leading-relaxed" x-text="noti.message"></p>
                            <p class="text-[11px] text-app-muted opacity-70 mt-1.5" x-text="noti.created_at_human"></p>
                        </div>
                        {{-- Chấm chưa đọc --}}
                        <div x-show="!noti.is_read" class="shrink-0 pt-1.5">
                            <span class="block w-2 h-2 rounded-full bg-primary"></span>
                        </div>
                    </a>
                </template>
            </div>

            {{-- Spinner khi đang tải thêm --}}
            <div x-show="panelLoading" class="flex items-center justify-center py-4 shrink-0 bg-inherit">
                <div class="size-5 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
            </div>

            {{-- Rỗng --}}
            <div x-show="!panelLoading && allNotifications.length === 0"
                class="flex flex-col items-center justify-center h-full py-20 px-6 text-app-muted">
                <span class="material-symbols-outlined text-[56px] mb-3 opacity-40">notifications_off</span>
                <p class="text-base font-semibold mb-1">{{ __('No notifications') }}</p>
                <p class="text-sm opacity-70">{{ __('You do not have any new notifications!') }}</p>
            </div>
        </div>
    </div>
</div>