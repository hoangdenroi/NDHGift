@props(['title' => 'Dashboard'])
<header
    class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-border-dark bg-white dark:bg-background-dark z-20">
    <div class="flex items-center gap-4">
        <button @click="window.innerWidth < 1024 ? sidebarOpen = !sidebarOpen : sidebarMini = !sidebarMini"
            class="text-slate-900 dark:text-white">
            <span class="material-symbols-outlined">menu</span>
        </button>
        <h2 class="text-slate-900 dark:text-white text-lg font-bold">{{ $title }}</h2>
    </div>
    <div class="flex items-center gap-4">
        <!-- Tìm kiếm -->
        <div class="relative hidden md:block">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[20px]">search</span>
            <input
                class="bg-slate-100 dark:bg-surface-dark border border-slate-200 dark:border-border-dark text-slate-900 dark:text-white text-sm rounded-lg pl-10 pr-4 py-2 w-64 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary placeholder-slate-400 dark:placeholder-slate-500"
                placeholder="Tìm kiếm..." type="text" />
        </div>

        <!-- Tìm kiếm trên thiết bị di động (Alpine.js) -->
        <div class="relative md:hidden" x-data="{ isSearchOpen: false }">
            <button @click="isSearchOpen = !isSearchOpen"
                :class="isSearchOpen ? 'text-primary' : 'text-gray-500 dark:text-slate-400'"
                class="flex items-center justify-center size-9 hover:text-gray-900 dark:hover:text-white transition-colors">
                <span class="material-symbols-outlined text-[20px]">search</span>
            </button>

            <!-- Khung tìm kiếm Dropdown -->
            <div x-show="isSearchOpen" x-cloak x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-[-50px] top-full mt-3 w-[280px] sm:w-[320px] bg-white dark:bg-surface-dark border border-gray-200 dark:border-border-dark rounded-lg shadow-xl p-3 z-50 origin-top-right">
                <!-- Mũi tên chỉ hướng -->
                <div
                    class="absolute -top-[5px] right-[58px] size-2.5 bg-white dark:bg-surface-dark border-t border-l border-gray-200 dark:border-border-dark transform rotate-45">
                </div>

                <!-- Input Tìm kiếm -->
                <div class="relative">
                    <input type="text" placeholder="Tìm kiếm..." x-ref="mobileSearchInput"
                        @keydown.escape="isSearchOpen = false"
                        class="w-full pl-10 pr-4 py-2.5 bg-gray-50 dark:bg-background-dark border border-gray-200 dark:border-border-dark rounded-lg text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-all shadow-sm">
                    <span
                        class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 dark:text-slate-500 text-[20px]">search</span>
                </div>
            </div>

            <!-- Backdrop đóng khi click ra ngoài -->
            <div x-show="isSearchOpen" @click="isSearchOpen = false" x-cloak class="fixed inset-0 z-40 cursor-default">
            </div>
        </div>

        <!-- Hành động -->
        <div class="flex items-center gap-2">
            <button @click="isPanelOpen = !isPanelOpen"
                class="flex items-center justify-center size-9 rounded-lg bg-slate-100 dark:bg-surface-dark border border-slate-200 dark:border-border-dark text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-500 transition-colors">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
            </button>
            <button x-data="{ isDark: document.documentElement.classList.contains('dark') }" @click="
                    isDark = !isDark;
                    document.documentElement.classList.toggle('dark', isDark);
                    let theme = JSON.parse(localStorage.getItem('theme') || '{}');
                    theme.mode = isDark ? 'dark' : 'light';
                    localStorage.setItem('theme', JSON.stringify(theme));
                "
                class="flex items-center justify-center size-9 rounded-lg bg-slate-100 dark:bg-surface-dark border border-slate-200 dark:border-border-dark text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-500 transition-colors">
                <span x-show="isDark" class="material-symbols-outlined text-[20px]">light_mode</span>
                <span x-show="!isDark" class="material-symbols-outlined text-[20px]">dark_mode</span>
            </button>
        </div>
        @php
            $userAvatar = auth()->user()->avatar_url;
            $finalAvatar = $userAvatar
                ? (str_starts_with($userAvatar, 'http')
                    ? $userAvatar
                    : asset('storage/' . $userAvatar))
                : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->fullname) . '&background=random';
        @endphp
        <div class="relative" x-data="{ openProfile: false }">
            <button @click="openProfile = !openProfile"
                class="flex items-center rounded-full hover:bg-app-surface/80 cursor-pointer transition-colors p-0.5 outline-none focus:ring-0"
                :class="openProfile ? 'bg-app-surface' : ''">
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-8 sm:size-9 relative border border-app-border"
                    data-alt="User avatar profile picture" style='background-image: url("{{ $finalAvatar }}");'>
                    <div
                        class="absolute bottom-0 right-0 size-2 sm:size-2.5 bg-green-500 border-2 border-app-main rounded-full">
                    </div>
                </div>
            </button>

            {{-- Backdrop di động để đóng dropdown --}}
            <div x-show="openProfile" @click="openProfile = false" x-cloak class="sm:hidden fixed inset-0 z-[199]">
            </div>

            <!-- Dropdown Menu -->
            <div x-show="openProfile" @click.away="openProfile = false" @keydown.escape.window="openProfile = false"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                x-cloak class="fixed top-[56px] right-3 w-[calc(100vw-24px)] max-w-[224px]
                       sm:absolute sm:top-full sm:left-auto sm:right-0 sm:translate-x-0 sm:mt-2 sm:w-56
                       bg-app-surface border border-app-border rounded-xl shadow-xl z-[200] origin-top">

                {{-- Mũi tên trỏ vào icon --}}
                <div x-ref="arrowOuter" x-effect="
                        if(openProfile) {
                            $nextTick(() => {
                                let btn = $el.closest('.relative').querySelector('.cursor-pointer');
                                let btnRect = btn.getBoundingClientRect();
                                let btnCenter = btnRect.left + btnRect.width / 2;
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

                {{-- Thông tin quản trị viên --}}
                <div class="px-4 py-3 border-b border-app-border relative z-10 bg-inherit rounded-t-xl">
                    <p class="text-sm font-bold text-app-text truncate">{{ auth()->user()->fullname }}</p>
                    <p class="text-xs text-app-muted truncate">{{ auth()->user()->email }}</p>
                </div>

                <div class="py-1">
                    <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-app-muted">home</span>
                        Trang chủ
                    </a>

                    <a href="#"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                        <span class="material-symbols-outlined text-[18px] text-app-muted">settings</span>
                        Cài đặt
                    </a>
                </div>

                <div class="border-t border-app-border py-1 rounded-b-xl">
                    <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors w-full text-left">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>