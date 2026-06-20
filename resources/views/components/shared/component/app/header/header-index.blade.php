@props(['title' => 'Dashboard'])
<header
    class="flex items-center justify-between px-3 py-2 sm:px-6 sm:py-4 border-b border-app-border bg-app-surface z-50">
    <div class="flex items-center gap-2 sm:gap-4">
        <img src="{{ asset('NDHGift.png') }}" alt="Logo" class="size-10">
        <h2 class="text-app-text text-sm sm:text-lg font-bold truncate max-w-[120px] sm:max-w-none leading-tight">
            NDHGift
        </h2>
    </div>
    <div class="flex items-center gap-2 sm:gap-4">
        {{-- <x-shared.layouts.app.ui.header-search /> --}}

        <!-- Actions -->
        <div class="flex items-center gap-1 sm:gap-2">
            {{-- <x-shared.layouts.app.ui.header-mini-menu /> --}}
            <x-shared.component.app.header.header-ui.header-language />
            {{-- <x-shared.layouts.app.ui.header-mode /> --}}
            <x-shared.component.app.header.header-ui.header-notifications />
            {{-- <x-shared.layouts.app.ui.header-cart-dropdown /> --}}
        </div>

        @auth
            @php
                $userAvatar = auth()->user()->avatar_url;
                $finalAvatar = $userAvatar
                    ? (str_starts_with($userAvatar, 'http')
                        ? $userAvatar
                        : asset('storage/' . $userAvatar))
                    : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->username) . '&background=random';
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

                {{-- Backdrop trên mobile để đóng dropdown --}}
                <div x-show="openProfile" @click="openProfile = false" x-cloak class="sm:hidden fixed inset-0 z-[199]">
                </div>

                <!-- Dropdown Menu -->
                <div x-show="openProfile" @click.away="openProfile = false" @keydown.escape.window="openProfile = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                    x-cloak
                    class="fixed top-[56px] right-3 w-[calc(100vw-24px)] max-w-[224px]
                                                                                sm:absolute sm:top-full sm:left-auto sm:right-0 sm:translate-x-0 sm:mt-2 sm:w-56
                                                                                bg-app-surface border border-app-border rounded-xl shadow-xl z-[200] origin-top">

                    {{-- Mũi tên trỏ vào icon (tự động tính vị trí) --}}
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

                    {{-- Thông tin user --}}
                    <div class="px-4 py-3 border-b border-app-border relative z-10 bg-inherit rounded-t-xl">
                        <p class="text-sm font-bold text-app-text truncate">{{ auth()->user()->fullname }}</p>
                        <p class="text-xs text-app-muted truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <div class="py-1">
                        @if (auth()->user()->role === 'admin')
                            <a target="_blank" href="#"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                                <span class="material-symbols-outlined text-[18px] text-app-muted">admin_panel_settings</span>
                                Trang quản trị
                            </a>
                        @endif
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-app-muted">person</span>
                            Hồ sơ
                        </a>

                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-app-muted">receipt</span>
                            Hóa đơn
                        </a>

                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-app-muted">lock</span>
                            Bảo mật
                        </a>

                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-app-muted">settings</span>
                            Cài đặt
                        </a>
                    </div>

                    <div class="border-t border-app-border py-1 rounded-b-xl">
                        <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-app-muted">menu_book</span>
                            Điều khoản & Dịch vụ
                        </a>

                        {{-- <a href="#"
                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-app-text hover:bg-app-surface/50 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-app-muted">code_xml</span>
                            Tài liệu API
                        </a> --}}
                    </div>

                    <div class="border-t border-app-border py-1 rounded-b-xl">
                        <form method="POST" action="{{ route('logout') }}">
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
        @else
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}"
                    class="px-4 py-2 text-sm font-medium text-app-text hover:bg-app-surface rounded-lg transition-colors">
                    Đăng nhập
                </a>
            </div>
        @endauth
    </div>
</header>