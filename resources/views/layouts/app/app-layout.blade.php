<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'NDHGift' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="shortcut icon" href="{{ asset('NDHGift.jpg') }}" type="image/x-icon">
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
    x-data="{ sidebarOpen: false, sidebarMini: JSON.parse(localStorage.getItem('navigation') || '{}').sidebarMini === true, isPanelOpen: false }"
    x-init="$watch('sidebarMini', value => {
        const nav = { ...JSON.parse(localStorage.getItem('navigation') || '{}'), sidebarMini: value };
        localStorage.setItem('navigation', JSON.stringify(nav));
        if ({{ auth()->check() ? 'true' : 'false' }}) {
            fetch('/api/v1/settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                body: JSON.stringify({ key: 'navigation', value: nav })
            });
        }
    })" class="bg-app-main text-app-text font-display overflow-hidden flex h-screen w-full">
    <!-- Overlay backdrop khi sidebar mở trên mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-30 lg:hidden" x-cloak></div>

    <!-- Sidebar -->
    <x-shared.layouts.app.nav />

    <!-- Panel Thông báo -->
    {{-- <x-admin.panel-admin-content /> --}}

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden relative">
        <!-- Header -->
        <x-shared.layouts.app.header :title="$title ?? 'Dashboard'" />

        <!-- Scrollable Content -->
        <div id="app-scroll-container" class="flex-1 overflow-y-auto scroll-smooth flex flex-col">
            <div class="flex-1 p-6">
                <div class="flex flex-col gap-6 max-w-[1400px] mx-auto">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer nằm ở cuối vùng cuộn -->
            <div class="{{ ($hideFooterMobile ?? false) ? 'hidden md:block' : '' }}">
                <x-shared.layouts.app.footer />
            </div>
        </div>
    </main>

    {{-- Toast Notifications Component --}}
    <x-shared.layouts.toast />

    {{-- Dispatch toast từ session flash --}}
    @if(session('success') || session('error'))
        <script>
            (function () {
                const showToast = () => {
                    @if(session('success'))
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: 'Thành công', message: @js(session('success')) }
                        }));
                    @endif
                    @if(session('error'))
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', title: 'Lỗi', message: @js(session('error')) }
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
                            detail: { type: 'success', title: 'Nạp tiền thành công', message: e.message }
                        }));

                        // Phát custom event để các component Alpine.js tự cập nhật số dư
                        window.dispatchEvent(new CustomEvent('balance-updated', {
                            detail: { new_balance: e.new_balance, amount: e.amount }
                        }));
                    });
            }
        </script>
    @endif

    @stack('scripts')
</body>

</html>