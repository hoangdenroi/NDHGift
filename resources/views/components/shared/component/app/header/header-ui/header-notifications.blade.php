<div class="relative" x-data="{
    notiOpen: false,
    allNotiOpen: false,
    notifications: [],
    unreadCount: 0,

    toggleOpen() {
        this.notiOpen = !this.notiOpen;
    },

    // Mở panel toàn bộ thông báo, đóng dropdown nhỏ
    openAllNotifications() {
        this.notiOpen = false;
        this.allNotiOpen = true;
        document.body.style.overflow = 'hidden';
    },

    // Đóng panel toàn bộ thông báo
    closeAllNotifications() {
        this.allNotiOpen = false;
        document.body.style.overflow = '';
    },

    notiIcon(type) {
        const map = {
            order_cancelled: 'cancel',
            order_completed: 'check_circle',
            order_refund: 'currency_exchange',
        };
        return map[type] || 'notifications';
    },

    notiColor(type) {
        const map = {
            order_cancelled: 'text-rose-500 bg-rose-50 dark:bg-rose-500/10',
            order_completed: 'text-emerald-500 bg-emerald-50 dark:bg-emerald-500/10',
            order_refund: 'text-blue-500 bg-blue-50 dark:bg-blue-500/10',
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
            <h3 class="font-bold text-app-text text-[15px]">Thông báo</h3>
            <button @click="openAllNotifications()" class="text-sm text-app-muted hover:text-app-text transition-colors">Xem tất cả</button>
        </div>

        {{-- Danh sách thông báo --}}
        <div x-show="notifications.length > 0" class="max-h-[60vh] sm:max-h-[400px] overflow-y-auto scrollbar-hide">
            <template x-for="noti in notifications" :key="noti.id">
                <a :href="noti.action_url || '#'" @click="notiOpen = false"
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
                        <p class="text-[11px] text-app-muted opacity-80 mt-1" x-text="noti.created_at"></p>
                    </div>
                </a>
            </template>
        </div>

        {{-- Rỗng --}}
        <div x-show="notifications.length === 0"
            class="flex flex-col items-center justify-center py-8 px-4 text-app-muted">
            <span class="material-symbols-outlined text-[40px] mb-2 opacity-50">notifications_off</span>
            <p class="text-sm font-medium">Bạn chưa có thông báo mới nào!</p>
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
                <h2 class="text-base font-bold text-app-text">Tất cả thông báo</h2>
                <span x-show="unreadCount > 0" x-cloak
                    class="inline-flex items-center justify-center h-5 min-w-[20px] px-1.5 rounded-full bg-rose-500 text-[11px] font-bold text-white"
                    x-text="unreadCount"></span>
            </div>
            <button @click="closeAllNotifications()"
                class="flex items-center justify-center size-9 rounded-lg hover:bg-app-surface/80 text-app-muted hover:text-app-text transition-colors"
                aria-label="Đóng">
                <span class="material-symbols-outlined text-[22px]">close</span>
            </button>
        </div>

        {{-- Danh sách thông báo — scroll toàn bộ --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            {{-- Có thông báo --}}
            <div x-show="notifications.length > 0">
                <template x-for="noti in notifications" :key="'all-' + noti.id">
                    <a :href="noti.action_url || '#'" @click="closeAllNotifications()"
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
                            <p class="text-[11px] text-app-muted opacity-70 mt-1.5" x-text="noti.created_at"></p>
                        </div>
                        {{-- Chấm chưa đọc --}}
                        <div x-show="!noti.is_read" class="shrink-0 pt-1.5">
                            <span class="block w-2 h-2 rounded-full bg-primary"></span>
                        </div>
                    </a>
                </template>
            </div>

            {{-- Rỗng --}}
            <div x-show="notifications.length === 0"
                class="flex flex-col items-center justify-center h-full py-20 px-6 text-app-muted">
                <span class="material-symbols-outlined text-[56px] mb-3 opacity-40">notifications_off</span>
                <p class="text-base font-semibold mb-1">Không có thông báo</p>
                <p class="text-sm opacity-70">Bạn chưa có thông báo mới nào!</p>
            </div>
        </div>
    </div>
</div>