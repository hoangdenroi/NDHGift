{{-- Component Cài đặt tài khoản & trải nghiệm --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm" x-data="{
    settingTab: 'menu', // 'menu', 'theme', 'language', 'notifications', 'security'
    activeSecurityCollapse: null, // null, 'password', 'email'
    themeMode: (function() {
        try {
            return JSON.parse(localStorage.getItem('theme') || '{}').mode || 'auto';
        } catch(e) {
            return 'auto';
        }
    })(),
    accentColor: (function() {
        try {
            return JSON.parse(localStorage.getItem('theme') || '{}').primaryColor || '#0d59f2';
        } catch(e) {
            return '#0d59f2';
        }
    })(),
    emailNotification: true,
    pushNotification: true,
    showCurrentPass: false,
    showNewPass: false,
    showConfirmPass: false,
    saveTimeouts: {},
    colors: [
        { hex: '#0d59f2', bg: 'bg-[#0d59f2]', ring: 'ring-[#0d59f2]' },
        { hex: '#3b82f6', bg: 'bg-blue-500', ring: 'ring-blue-500' },
        { hex: '#6366f1', bg: 'bg-indigo-500', ring: 'ring-indigo-500' },
        { hex: '#8b5cf6', bg: 'bg-violet-500', ring: 'ring-violet-500' },
        { hex: '#ec4899', bg: 'bg-pink-500', ring: 'ring-pink-500' },
        { hex: '#f43f5e', bg: 'bg-rose-500', ring: 'ring-rose-500' },
        { hex: '#ef4444', bg: 'bg-red-500', ring: 'ring-red-500' },
        { hex: '#f97316', bg: 'bg-orange-500', ring: 'ring-orange-500' },
        { hex: '#f59e0b', bg: 'bg-amber-500', ring: 'ring-amber-500' },
        { hex: '#22c55e', bg: 'bg-green-500', ring: 'ring-green-500' },
        { hex: '#10b981', bg: 'bg-emerald-500', ring: 'ring-emerald-500' },
        { hex: '#14b8a6', bg: 'bg-teal-500', ring: 'ring-teal-500' },
        { hex: '#06b6d4', bg: 'bg-cyan-500', ring: 'ring-cyan-500' },
        { hex: '#0ea5e9', bg: 'bg-sky-500', ring: 'ring-sky-500' },
        { hex: '#64748b', bg: 'bg-slate-500', ring: 'ring-slate-500' }
    ],
    applyTheme() {
        if (this.themeMode === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (this.themeMode === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        if (this.accentColor) {
            document.documentElement.style.setProperty('--color-primary', this.accentColor);
        }
        localStorage.setItem('theme', JSON.stringify({ mode: this.themeMode, primaryColor: this.accentColor }));
        this.saveSettings('theme', { mode: this.themeMode, primaryColor: this.accentColor });
    },
    saveSettings(key, value) {
        if (this.saveTimeouts[key]) {
            clearTimeout(this.saveTimeouts[key]);
        }
        this.saveTimeouts[key] = setTimeout(() => {
            fetch('/api/v1/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ key, value })
            })
            .then(response => {
                if (!response.ok) throw new Error();
                return response.json();
            })
            .catch(() => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', title: 'Lỗi đồng bộ', message: 'Không thể đồng bộ cài đặt lên máy chủ.' }
                }));
            });
        }, 500);
    },
    setTheme(mode) {
        this.themeMode = mode;
        this.applyTheme();
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { type: 'success', title: 'Thành công', message: 'Đã cập nhật chế độ hiển thị.' }
        }));
    },
    setAccentColor(color) {
        this.accentColor = color;
        this.applyTheme();
        window.dispatchEvent(new CustomEvent('toast', {
            detail: { type: 'success', title: 'Thành công', message: 'Đã cập nhật màu chủ đạo.' }
        }));
    }
}" x-init="() => {
    // Khởi tạo các giá trị ban đầu từ Database
    @php
        $userSettings = auth()->user()->settings ?? [];
        $themeSettings = $userSettings['theme'] ?? [];
        $notiSettings = $userSettings['notifications'] ?? ['email' => true, 'push' => true];
        $dbMode = $themeSettings['mode'] ?? null;
        $dbColor = $themeSettings['primaryColor'] ?? null;
    @endphp

    @if($dbMode)
        themeMode = '{{ $dbMode }}';
    @endif
    @if($dbColor)
        accentColor = '{{ $dbColor }}';
    @endif

    emailNotification = {{ ($notiSettings['email'] ?? true) ? 'true' : 'false' }};
    pushNotification = {{ ($notiSettings['push'] ?? true) ? 'true' : 'false' }};

    // Áp dụng theme và màu sắc ban đầu
    if (themeMode) {
        if (themeMode === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (themeMode === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }
    if (accentColor) {
        document.documentElement.style.setProperty('--color-primary', accentColor);
    }

    // Đăng ký watch sau khi kết thúc chu kỳ init ban đầu để tránh trigger API thừa
    $nextTick(() => {
        $watch('emailNotification', value => {
            saveSettings('notifications', { email: value, push: pushNotification });
        });
        $watch('pushNotification', value => {
            saveSettings('notifications', { email: emailNotification, push: value });
        });
    });

    setTimeout(() => {
        @if($errors->updatePassword->any())
            settingTab = 'security';
            activeSecurityCollapse = 'password';
            @foreach($errors->updatePassword->all() as $error)
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', title: 'Lỗi bảo mật', message: '{{ $error }}' }
                }));
            @endforeach
        @endif
        @if($errors->has('email'))
            settingTab = 'security';
            activeSecurityCollapse = 'email';
            @foreach($errors->get('email') as $error)
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', title: 'Lỗi cập nhật', message: '{{ $error }}' }
                }));
            @endforeach
        @endif
        @if(session('status') === 'password-updated')
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', title: 'Thành công', message: 'Mật khẩu đã được cập nhật thành công.' }
            }));
        @endif
        @if(session('success') && str_contains(session('success'), 'email'))
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'success', title: 'Thành công', message: '{{ session('success') }}' }
            }));
        @endif
    }, 150);
}">

    <!-- ==================== HEADER CHUNG ==================== -->
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <!-- Nút Back tự động thay đổi điểm đến dựa trên Tab hiện tại -->
        <button @click="settingTab === 'menu' ? setActiveAction('menu') : settingTab = 'menu'"
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <!-- Tiêu đề Header thay đổi dựa trên Tab cài đặt đang hoạt động -->
            <template x-if="settingTab === 'menu'">
                <div>
                    <h2 class="text-base font-bold text-app-text">Cài đặt hệ thống</h2>
                    <p class="text-sm text-app-muted mt-0.5">Tùy biến tài khoản và cấu hình trải nghiệm sử dụng</p>
                </div>
            </template>
            <template x-if="settingTab === 'theme'">
                <div>
                    <h2 class="text-base font-bold text-app-text">Chế độ hiển thị</h2>
                    <p class="text-sm text-app-muted mt-0.5">Tùy chỉnh giao diện Sáng, Tối hoặc Tự động</p>
                </div>
            </template>
            <template x-if="settingTab === 'language'">
                <div>
                    <h2 class="text-base font-bold text-app-text">Ngôn ngữ hệ thống</h2>
                    <p class="text-sm text-app-muted mt-0.5">Cấu hình ngôn ngữ hiển thị của ứng dụng</p>
                </div>
            </template>
            <template x-if="settingTab === 'notifications'">
                <div>
                    <h2 class="text-base font-bold text-app-text">Cấu hình nhận thông báo</h2>
                    <p class="text-sm text-app-muted mt-0.5">Quản lý các sự kiện nhận thông báo đẩy và email</p>
                </div>
            </template>
            <template x-if="settingTab === 'security'">
                <div>
                    <h2 class="text-base font-bold text-app-text">Bảo mật tài khoản</h2>
                    <p class="text-sm text-app-muted mt-0.5">Cập nhật mật khẩu, email đăng nhập của bạn</p>
                </div>
            </template>
        </div>
    </div>

    <!-- ==================== NỘI DUNG CHI TIẾT ==================== -->
    <div class="p-6">

        <!-- Tab 1: Danh sách chức năng cài đặt (Menu chính) -->
        <div x-show="settingTab === 'menu'" class="space-y-3 animate-fade-in">
            {{-- Giao diện --}}
            <button @click="settingTab = 'theme'"
                class="flex items-center justify-between w-full p-4 rounded-xl border border-app-border bg-app-main/20 hover:bg-primary/5 hover:border-primary/30 transition-all duration-300 group text-left">
                <div class="flex items-center gap-4">
                    <div
                        class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <span class="material-symbols-outlined text-[22px]">palette</span>
                    </div>
                    <div>
                        <span
                            class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors block">Giao
                            diện</span>
                        <span class="text-xs text-app-muted mt-0.5 block">Chọn chế độ hiển thị Sáng, Tối hoặc Tự
                            động</span>
                    </div>
                </div>
                <span
                    class="material-symbols-outlined text-[20px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
            </button>

            {{-- Ngôn ngữ --}}
            <button @click="settingTab = 'language'"
                class="flex items-center justify-between w-full p-4 rounded-xl border border-app-border bg-app-main/20 hover:bg-primary/5 hover:border-primary/30 transition-all duration-300 group text-left">
                <div class="flex items-center gap-4">
                    <div
                        class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <span class="material-symbols-outlined text-[22px]">language</span>
                    </div>
                    <div>
                        <span
                            class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors block">Ngôn
                            ngữ</span>
                        <span class="text-xs text-app-muted mt-0.5 block">Cấu hình ngôn ngữ hiển thị hệ thống</span>
                    </div>
                </div>
                <span
                    class="material-symbols-outlined text-[20px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
            </button>

            {{-- Thông báo --}}
            <button @click="settingTab = 'notifications'"
                class="flex items-center justify-between w-full p-4 rounded-xl border border-app-border bg-app-main/20 hover:bg-primary/5 hover:border-primary/30 transition-all duration-300 group text-left">
                <div class="flex items-center gap-4">
                    <div
                        class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <span class="material-symbols-outlined text-[22px]">notifications</span>
                    </div>
                    <div>
                        <span
                            class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors block">Thông
                            báo</span>
                        <span class="text-xs text-app-muted mt-0.5 block">Cấu hình thông báo qua Email và thông báo
                            đẩy</span>
                    </div>
                </div>
                <span
                    class="material-symbols-outlined text-[20px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
            </button>

            {{-- Bảo mật --}}
            <button @click="settingTab = 'security'"
                class="flex items-center justify-between w-full p-4 rounded-xl border border-app-border bg-app-main/20 hover:bg-primary/5 hover:border-primary/30 transition-all duration-300 group text-left">
                <div class="flex items-center gap-4">
                    <div
                        class="size-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                        <span class="material-symbols-outlined text-[22px]">security</span>
                    </div>
                    <div>
                        <span
                            class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors block">Bảo
                            mật</span>
                        <span class="text-xs text-app-muted mt-0.5 block">Đổi mật khẩu và cài đặt an toàn tài
                            khoản</span>
                    </div>
                </div>
                <span
                    class="material-symbols-outlined text-[20px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all">chevron_right</span>
            </button>
        </div>

        <!-- Tab 2: Cấu hình Chế độ hiển thị (Theme) -->
        <div x-show="settingTab === 'theme'" x-cloak class="space-y-6 animate-fade-in">
            {{-- Chế độ sáng/tối --}}
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-app-text">Chế độ hiển thị</h3>
                <div class="grid grid-cols-3 gap-3">
                    <button @click="setTheme('light')"
                        :class="themeMode === 'light' ? 'ring-2 ring-primary border-primary bg-primary/5' : 'border-app-border hover:border-app-muted bg-app-main/10'"
                        class="flex flex-col items-center gap-3 p-4 rounded-xl border transition-all duration-300">
                        <div class="size-12 rounded-full bg-amber-100 flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-amber-500">light_mode</span>
                        </div>
                        <span class="text-sm font-semibold text-app-text">Sáng</span>
                    </button>
                    <button @click="setTheme('dark')"
                        :class="themeMode === 'dark' ? 'ring-2 ring-primary border-primary bg-primary/5' : 'border-app-border hover:border-app-muted bg-app-main/10'"
                        class="flex flex-col items-center gap-3 p-4 rounded-xl border transition-all duration-300">
                        <div class="size-12 rounded-full bg-slate-700 flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-slate-300">dark_mode</span>
                        </div>
                        <span class="text-sm font-semibold text-app-text">Tối</span>
                    </button>
                    <button @click="setTheme('auto')"
                        :class="themeMode === 'auto' ? 'ring-2 ring-primary border-primary bg-primary/5' : 'border-app-border hover:border-app-muted bg-app-main/10'"
                        class="flex flex-col items-center gap-3 p-4 rounded-xl border transition-all duration-300">
                        <div
                            class="size-12 rounded-full bg-gradient-to-br from-amber-100 to-slate-700 flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-white">contrast</span>
                        </div>
                        <span class="text-sm font-semibold text-app-text">Tự động</span>
                    </button>
                </div>
            </div>

            {{-- Màu chủ đạo --}}
            <div class="space-y-3 pt-2">
                <h3 class="text-sm font-semibold text-app-text">Màu chủ đạo</h3>
                <div class="flex flex-wrap gap-2.5">
                    <template x-for="color in colors" :key="color.hex">
                        <button @click="setAccentColor(color.hex)"
                            :class="[color.bg, accentColor === color.hex ? 'ring-2 ring-offset-2 ring-primary dark:ring-offset-[#111827]' : '']"
                            class="relative size-8 rounded-full hover:scale-110 transition-transform focus:outline-none">
                            <span x-show="accentColor === color.hex"
                                class="material-symbols-outlined absolute inset-0 flex items-center justify-center text-white text-[16px] select-none">check</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Tab 3: Cấu hình Ngôn ngữ -->
        <div x-show="settingTab === 'language'" x-cloak class="space-y-4 animate-fade-in">
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route(Route::currentRouteName() ?? 'app.profile.index', array_merge(request()->route()->parameters(), ['locale' => 'vi'])) }}"
                    class="flex-1 flex items-center justify-between p-4 rounded-xl border {{ app()->getLocale() === 'vi' ? 'border-primary bg-primary/5 text-primary' : 'border-app-border hover:bg-primary/5 hover:border-primary/30 text-app-text bg-app-main/10' }} text-sm font-semibold transition-all">
                    <div class="flex items-center gap-3">
                        <img src="/assets/images/flags/vn.png" alt="VN"
                            class="w-6 h-4 object-cover rounded-sm shadow-sm"
                            onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/vn.svg'" />
                        <span>Tiếng Việt</span>
                    </div>
                    <template x-if="'{{ app()->getLocale() }}' === 'vi'">
                        <span class="material-symbols-outlined text-primary text-[20px]">check_circle</span>
                    </template>
                </a>

                <a href="{{ route(Route::currentRouteName() ?? 'app.profile.index', array_merge(request()->route()->parameters(), ['locale' => 'en'])) }}"
                    class="flex-1 flex items-center justify-between p-4 rounded-xl border {{ app()->getLocale() === 'en' ? 'border-primary bg-primary/5 text-primary' : 'border-app-border hover:bg-primary/5 hover:border-primary/30 text-app-text bg-app-main/10' }} text-sm font-semibold transition-all">
                    <div class="flex items-center gap-3">
                        <img src="/assets/images/flags/us.png" alt="US"
                            class="w-6 h-4 object-cover rounded-sm shadow-sm"
                            onerror="this.src='https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.4.3/flags/4x3/us.svg'" />
                        <span>English</span>
                    </div>
                    <template x-if="'{{ app()->getLocale() }}' === 'en'">
                        <span class="material-symbols-outlined text-primary text-[20px]">check_circle</span>
                    </template>
                </a>
            </div>
        </div>

        <!-- Tab 4: Cấu hình Nhận thông báo -->
        <div x-show="settingTab === 'notifications'" x-cloak class="space-y-4 animate-fade-in">
            <div class="space-y-3">
                <div @click="emailNotification = !emailNotification"
                    class="flex items-center justify-between p-4 rounded-xl border border-app-border hover:border-primary/30 hover:bg-primary/5 transition-colors cursor-pointer group bg-app-main/10 select-none gap-4">
                    <div class="flex flex-col gap-0.5 pr-4 flex-1">
                        <span
                            class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors">Thông
                            báo qua Email</span>
                        <span class="text-xs text-app-muted leading-relaxed">Nhận các thông báo cần thiết qua
                            email(bảo mật, giao dịch, khuyến mãi,...).</span>
                    </div>
                    <button type="button"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none items-center"
                        :class="emailNotification ? 'bg-primary' : 'bg-app-border/60 dark:bg-app-border'">
                        <span
                            class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="emailNotification ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>

                <div @click="pushNotification = !pushNotification"
                    class="flex items-center justify-between p-4 rounded-xl border border-app-border hover:border-primary/30 hover:bg-primary/5 transition-colors cursor-pointer group bg-app-main/10 select-none gap-4">
                    <div class="flex flex-col gap-0.5 pr-4 flex-1">
                        <span
                            class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors">Thông
                            báo đẩy (Push)</span>
                        <span class="text-xs text-app-muted leading-relaxed">Nhận thông báo trực tiếp trên trình duyệt
                            khi có sự kiện quan trọng(toast và thông báo từ trình duyệt).</span>
                    </div>
                    <button type="button"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none items-center"
                        :class="pushNotification ? 'bg-primary' : 'bg-app-border/60 dark:bg-app-border'">
                        <span
                            class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="pushNotification ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab 5: Cấu hình Bảo mật (Đổi mật khẩu & Email) -->
        <div x-show="settingTab === 'security'" x-cloak class="space-y-4 animate-fade-in max-w-lg">

            {{-- Accordion 1: Cập nhật mật khẩu --}}
            <div class="border border-app-border rounded-xl bg-app-main/10 overflow-hidden transition-all duration-300">
                <!-- Tiêu đề Accordion -->
                <button type="button"
                    @click="activeSecurityCollapse = (activeSecurityCollapse === 'password' ? null : 'password')"
                    class="w-full flex items-center justify-between p-4 hover:bg-primary/5 transition-all text-left group">
                    <div class="flex items-center gap-3">
                        <span
                            class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary transition-colors">lock</span>
                        <div class="flex flex-col gap-0.5">
                            <span
                                class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors">Cập
                                nhật mật khẩu</span>
                            <span class="text-[11px] text-app-muted">Lần thay đổi gần nhất: <span
                                    class="text-app-text text-primary">{{
    auth()->user()->last_change_password_at?->format('d/m/Y H:i') ?? 'Chưa từng thay đổi'}}</span></span>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                        :class="activeSecurityCollapse === 'password' ? 'rotate-90 text-primary' : ''">chevron_right</span>
                </button>

                <!-- Body Accordion (Form đổi mật khẩu) -->
                <div x-show="activeSecurityCollapse === 'password'"
                    x-transition:enter="transition-all ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition-all ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="px-4 pb-5 pt-2 border-t border-app-border/50 bg-app-surface/50">

                    <form method="post" action="{{ route('password.update', ['locale' => app()->getLocale()]) }}"
                        class="space-y-4">
                        @csrf
                        @method('put')

                        <div>
                            <label for="update_password_current_password"
                                class="block text-xs font-semibold text-app-text mb-1.5">Mật khẩu hiện tại</label>
                            <div class="relative">
                                <input id="update_password_current_password" name="current_password"
                                    :type="showCurrentPass ? 'text' : 'password'"
                                    class="w-full h-10 pl-3 pr-10 bg-app-main border border-app-border rounded-xl text-sm text-app-text focus:border-primary focus:ring-0 transition-colors outline-none"
                                    autocomplete="current-password" required placeholder="Mật khẩu hiện tại" />
                                <button type="button" @click="showCurrentPass = !showCurrentPass"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-app-muted hover:text-app-text focus:outline-none transition-colors">
                                    <span class="material-symbols-outlined text-[20px] select-none block"
                                        x-text="showCurrentPass ? 'visibility' : 'visibility_off'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="update_password_password"
                                class="block text-xs font-semibold text-app-text mb-1.5">Mật khẩu mới</label>
                            <div class="relative">
                                <input id="update_password_password" name="password"
                                    :type="showNewPass ? 'text' : 'password'"
                                    class="w-full h-10 pl-3 pr-10 bg-app-main border border-app-border rounded-xl text-sm text-app-text focus:border-primary focus:ring-0 transition-colors outline-none"
                                    autocomplete="new-password" required placeholder="Mật khẩu mới" />
                                <button type="button" @click="showNewPass = !showNewPass"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-app-muted hover:text-app-text focus:outline-none transition-colors">
                                    <span class="material-symbols-outlined text-[20px] select-none block"
                                        x-text="showNewPass ? 'visibility' : 'visibility_off'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="update_password_password_confirmation"
                                class="block text-xs font-semibold text-app-text mb-1.5">Xác nhận mật khẩu mới</label>
                            <div class="relative">
                                <input id="update_password_password_confirmation" name="password_confirmation"
                                    :type="showConfirmPass ? 'text' : 'password'"
                                    class="w-full h-10 pl-3 pr-10 bg-app-main border border-app-border rounded-xl text-sm text-app-text focus:border-primary focus:ring-0 transition-colors outline-none"
                                    autocomplete="new-password" required placeholder="Xác nhận mật khẩu mới" />
                                <button type="button" @click="showConfirmPass = !showConfirmPass"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-app-muted hover:text-app-text focus:outline-none transition-colors">
                                    <span class="material-symbols-outlined text-[20px] select-none block"
                                        x-text="showConfirmPass ? 'visibility' : 'visibility_off'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="h-10 px-5 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] flex items-center justify-center gap-2">
                                Lưu mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Accordion 2: Cập nhật Email --}}
            <div class="border border-app-border rounded-xl bg-app-main/10 overflow-hidden transition-all duration-300">
                <!-- Tiêu đề Accordion -->
                <button type="button"
                    @click="activeSecurityCollapse = (activeSecurityCollapse === 'email' ? null : 'email')"
                    class="w-full flex items-center justify-between p-4 hover:bg-primary/5 transition-all text-left group">
                    <div class="flex items-center gap-3">
                        <span
                            class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary transition-colors">mail</span>
                        <span class="text-sm font-semibold text-app-text group-hover:text-primary transition-colors">Cập
                            nhật email</span>
                    </div>
                    <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                        :class="activeSecurityCollapse === 'email' ? 'rotate-90 text-primary' : ''">chevron_right</span>
                </button>

                <!-- Body Accordion (Form đổi email) -->
                <div x-show="activeSecurityCollapse === 'email'"
                    x-transition:enter="transition-all ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition-all ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="px-4 pb-5 pt-2 border-t border-app-border/50 bg-app-surface/50">

                    <form method="post" action="{{ route('app.profile.update', ['locale' => app()->getLocale()]) }}"
                        class="space-y-4">
                        @csrf

                        <div>
                            <label for="update_email_address"
                                class="block text-xs font-semibold text-app-text mb-1.5">Địa chỉ Email mới</label>
                            <input id="update_email_address" name="email" type="email"
                                value="{{ old('email', auth()->user()->email) }}"
                                class="w-full h-10 px-3 bg-app-main border border-app-border rounded-xl text-sm text-app-text focus:border-primary focus:ring-0 transition-colors outline-none"
                                required placeholder="Địa chỉ email mới" />
                        </div>

                        <div class="pt-2">
                            <button type="submit"
                                class="h-10 px-5 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] flex items-center justify-center gap-2">
                                Lưu email
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>