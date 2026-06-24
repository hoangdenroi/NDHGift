<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'NDHGift' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="shortcut icon" href="{{ asset('NDHGift.png') }}" type="image/x-icon">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark ::-webkit-scrollbar-track {
            background: #101622;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #2d3646;
        }

        .dark ::-webkit-scrollbar-thumb:hover {
            background: #3b4354;
        }
    </style>

    {{-- Chặn Google Translate dịch nội dung icon font — tránh vỡ layout --}}
    <script>
        (function () {
            /**
             * Gắn attribute translate="no" và class "notranslate" cho icon font.
             * Google Translate sẽ bỏ qua các element có marker này.
             */
            function protectIcons(root) {
                root.querySelectorAll('.material-symbols-outlined:not(.notranslate)').forEach(function (el) {
                    el.classList.add('notranslate');
                    el.setAttribute('translate', 'no');
                });
            }

            // Xử lý icon có sẵn trong DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', function () { protectIcons(document); });
            } else {
                protectIcons(document);
            }

            // Xử lý icon được thêm động bởi Alpine.js / AJAX
            new MutationObserver(function (mutations) {
                mutations.forEach(function (m) {
                    m.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1) {
                            if (node.classList && node.classList.contains('material-symbols-outlined')) {
                                node.classList.add('notranslate');
                                node.setAttribute('translate', 'no');
                            }
                            protectIcons(node);
                        }
                    });
                });
            }).observe(document.documentElement, { childList: true, subtree: true });
        })();
    </script>

    <!-- Theme Initialization Script -->
    <script>
        (function () {
            try {
                const userSettings = {!! json_encode(auth()->check() ? auth()->user()->settings : null) !!};

                if (userSettings) {
                    // 1. Đồng bộ Theme
                    const theme = userSettings.theme;
                    if (theme) {
                        localStorage.setItem('theme', JSON.stringify(theme));
                    }

                    // 2. Đồng bộ Notifications
                    const notifications = userSettings.notifications;
                    if (notifications) {
                        localStorage.setItem('notifications', JSON.stringify(notifications));
                    }

                    // 3. Đồng bộ Language
                    const language = userSettings.language;
                    if (language) {
                        localStorage.setItem('language', language);
                    }

                    // 4. Đồng bộ Navigation
                    const navigation = userSettings.navigation;
                    if (navigation) {
                        let localNav = {};
                        try {
                            localNav = JSON.parse(localStorage.getItem('navigation')) || {};
                        } catch (e) { }

                        // Merge cấu trúc: Nếu LocalStorage đã có tự lưu sidebarMini thì giữ nguyên, và đè các cấu hình khác từ DB xuống
                        localStorage.setItem('navigation', JSON.stringify({ ...navigation, ...localNav }));
                    }
                }

                // --- Áp dụng Theme hiện tại ---
                let mode = 'auto';
                let primaryColor = '#0d59f2';

                let storedTheme = localStorage.getItem('theme');
                if (storedTheme) {
                    let config = JSON.parse(storedTheme);
                    mode = config.mode || mode;
                    primaryColor = config.primaryColor || primaryColor;
                }

                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (mode === 'dark' || (mode === 'auto' && prefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                if (primaryColor) {
                    document.documentElement.style.setProperty('--color-primary', primaryColor);
                }
            } catch (e) {
                console.error("Theme init error:", e);
            }
        })();
    </script>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    x-data="{ sidebarOpen: false, sidebarMini: false, isPanelOpen: false }"
    class="bg-app-main text-app-text font-display overflow-hidden flex h-screen w-full">
    <!-- Overlay backdrop khi sidebar mở trên mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-30 lg:hidden" x-cloak></div>

    <!-- Sidebar (Sử dụng sidebar hoặc navbar tùy cấu hình) -->

    <!-- Panel Thông báo -->
    {{-- <x-admin.panel-admin-content /> --}}

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Header -->
        <x-shared.component.app.header.header-index :title="$title ?? 'NDHGift'" />

        <!-- Scrollable Content -->
        <div id="app-scroll-container" class="flex-1 overflow-y-auto scroll-smooth flex flex-col">
            <div class="flex-1 p-6 pb-24 lg:pb-6">
                <div class="flex flex-col gap-6 max-w-[1400px] mx-auto">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer nằm ở cuối vùng cuộn -->
            <div class="{{ ($hideFooterMobile ?? false) ? 'hidden md:block' : '' }} pb-20 lg:pb-0">
                <x-shared.component.app.footer.footer-index />
            </div>
        </div>
    </main>

    {{-- Toast Notifications Component --}}
    <x-shared.ui.toast />

    {{-- Dispatch toast từ session flash --}}
    @if(session('success') || session('error'))
        <script>
            (function () {
                const showToast = () => {
                    @if(session('success'))
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: @js(__('Successful')), message: @js(session('success')) }
                        }));
                    @endif
                    @if(session('error'))
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', title: @js(__('Error')), message: @js(session('error')) }
                        }));
                    @endif
                             };
                if (window.Alpine) setTimeout(showToast, 50);
                else document.addEventListener('alpine:initialized', showToast);
            })();
        </script>
    @endif

    @if(auth()->check())
        <script type="module">
            window.userId = @json(auth()->id());
            if (window.userId && window.Echo) {
                window.Echo.private('App.Models.User.' + window.userId)
                    .stopListening('UserAccountLocked')
                    .stopListening('BalanceUpdated')
                    .listen('UserAccountLocked', (e) => {
                        // Redirect về '/', Middleware EnsureUserIsActive sẽ chặn lại, gỡ session và hiển thị Toast Login
                        window.location.href = '/';
                    })
                    .listen('BalanceUpdated', (e) => {
                        // Hiển thị toast thông báo nạp tiền thành công
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: @js(__('Top up successful')), message: e.message }
                        }));

                        // Phát custom event để các component Alpine.js tự cập nhật số dư
                        window.dispatchEvent(new CustomEvent('balance-updated', {
                            detail: { new_balance: e.new_balance, amount: e.amount }
                        }));
                    });
            }
        </script>
    @endif

    <!-- Bottom Navbar dành cho mobile/tablet -->
    <x-shared.component.app.navbar.navbar-index />

    {{-- Popup chúc mừng Điểm Danh Hàng Ngày --}}
    @if(session('checkin_success'))
        @php
            $checkinData = session('checkin_success');
            $streak = $checkinData['streak'] ?? 1;
            $xpAwarded = $checkinData['xp_awarded'] ?? 10;
        @endphp
        <div x-data="{ open: true }" x-show="open" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak>
            
            <div @click.outside="open = false" 
                 class="bg-app-surface border border-app-border rounded-2xl w-full max-w-md overflow-hidden shadow-2xl flex flex-col p-6 items-center text-center relative"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                 x-transition:leave-end="opacity-0 scale-95 translate-y-4">
                
                {{-- Icon trang trí --}}
                <div class="size-16 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 mb-4 animate-bounce">
                    <span class="material-symbols-outlined text-[32px]">workspace_premium</span>
                </div>
                
                <h3 class="text-lg font-extrabold text-app-text mb-1">Điểm Danh Thành Công!</h3>
                <p class="text-xs text-app-muted mb-6 leading-relaxed">
                    Bạn đã điểm danh ngày thứ <span class="text-amber-500 font-bold">{{ $streak }}</span> liên tiếp. Nhận ngay điểm thưởng XP!
                </p>
                
                {{-- XP Awarded Display --}}
                <div class="mb-6 flex flex-col items-center">
                    <span class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-500">
                        +{{ $xpAwarded }} XP
                    </span>
                    @if(($checkinData['bonus_awarded'] ?? 0) > 0)
                        <span class="text-[10px] text-green-500 font-bold mt-1 bg-green-500/10 px-2.5 py-0.5 rounded-full border border-green-500/20">
                            Đã cộng {{ $checkinData['bonus_awarded'] }} XP thưởng chuỗi 7 ngày!
                        </span>
                    @endif
                </div>

                {{-- 7-Day Streak Timeline --}}
                <div class="w-full flex items-center justify-between gap-1 bg-app-main border border-app-border p-3.5 rounded-xl mb-6">
                    @for($i = 1; $i <= 7; $i++)
                        <div class="flex flex-col items-center gap-1.5 flex-1 relative">
                            {{-- Trực quan hóa đường kết nối streak --}}
                            @if($i < 7)
                                <div class="absolute top-4 left-[calc(50%+10px)] right-[-50%] h-0.5 {{ $i < $streak ? 'bg-amber-500' : 'bg-app-border' }} z-0"></div>
                            @endif
                            
                            {{-- Trạng thái điểm từng ngày --}}
                            <div class="size-8 rounded-full border flex items-center justify-center z-10 transition-all duration-300
                                {{ $i < $streak ? 'bg-amber-500/20 border-amber-500 text-amber-500' : '' }}
                                {{ $i === $streak ? 'bg-gradient-to-tr from-amber-500 to-orange-500 border-amber-500 text-white shadow-lg shadow-amber-500/30 scale-110' : '' }}
                                {{ $i > $streak ? 'bg-app-surface border-app-border text-app-muted' : '' }}
                            ">
                                @if($i === 7)
                                    <span class="material-symbols-outlined text-[14px]">featured_seasonal_and_gifts</span>
                                @elseif($i < $streak)
                                    <span class="material-symbols-outlined text-[14px] font-bold">check</span>
                                @else
                                    <span class="text-[10px] font-bold">{{ $i }}</span>
                                @endif
                            </div>
                            <span class="text-[9px] font-semibold {{ $i == $streak ? 'text-amber-500 font-bold' : 'text-app-muted' }}">
                                T{{ $i }}
                            </span>
                        </div>
                    @endfor
                </div>

                <button @click="open = false" 
                        class="w-full h-11 bg-primary hover:bg-primary/90 text-white font-bold text-xs rounded-xl transition-all active:scale-[0.98] shadow-md shadow-primary/20">
                    Tuyệt vời!
                </button>
            </div>
        </div>
    @endif

    @stack('scripts')
</body>
</html>