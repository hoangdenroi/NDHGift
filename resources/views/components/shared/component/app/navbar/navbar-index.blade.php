@php
    // Kiểm tra route hiện tại để kích hoạt class active cho các tab
    $currentRoute = request()->route()->getName();
    $locale = app()->getLocale();

    // Định nghĩa danh sách các tab chính trên mobile navbar
    $navItems = [
        [
            'name' => 'Trang chủ',
            'route' => route('app.home.index', ['locale' => $locale]),
            'icon' => 'home',
            'active' => request()->routeIs('app.home.index*'),
        ],
        [
            'name' => 'Quà tặng',
            'route' => route('app.gift.index', ['locale' => $locale]),
            'icon' => 'redeem',
            'active' => request()->routeIs('app.gift.index*'),
        ],
        // Nút giữa (hình tròn to chứa dấu cộng) sẽ được render riêng
        [
            'name' => 'Hỗ trợ',
            'route' => route('app.support.index', ['locale' => $locale]),
            'icon' => 'forum',
            'active' => request()->routeIs('app.support.index*'),
        ],
        [
            'name' => 'Hồ sơ',
            'route' => route('app.profile.index', ['locale' => $locale]),
            'icon' => 'person',
            'active' => request()->routeIs('app.profile.index*'),
        ],
    ];
@endphp

<!-- Bottom Navbar dành cho Mobile và Tablet (Ẩn trên Desktop) -->
<div
    class="fixed bottom-0 left-0 right-0 z-40 bg-app-surface/90 backdrop-blur-lg border-t border-app-border/80 shadow-[0_-4px_20px_-4px_rgba(0,0,0,0.08)] lg:hidden transition-all duration-300">
    <div class="relative max-w-md mx-auto px-4 h-16 sm:h-18 flex items-center justify-between">

        <!-- Tab 1: Trang chủ -->
        <a href="{{ $navItems[0]['route'] }}"
            class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[0]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span
                class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[0]['active'] ? 'scale-110 font-filled' : '' }}">
                {{ $navItems[0]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[0]['name'] }}
            </span>
        </a>

        <!-- Tab 2: Quà tặng -->
        <a href="{{ $navItems[1]['route'] }}"
            class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[1]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span
                class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[1]['active'] ? 'scale-110' : '' }}">
                {{ $navItems[1]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[1]['name'] }}
            </span>
        </a>

        <!-- Cột giả ở giữa để giữ khoảng trống cho nút tròn to -->
        <div class="flex-1 flex justify-center items-center h-full relative">
            <!-- Vòng tròn đệm ngoài màu tối tạo chiều sâu và ôm lấy nút tròn (như ảnh vẽ khoanh đỏ) -->
            <div
                class="absolute -top-6 sm:-top-7 w-16 h-16 sm:w-[72px] sm:h-[72px] rounded-full bg-neutral-900 dark:bg-neutral-950 flex items-center justify-center shadow-lg shadow-black/40 border border-neutral-800/50">
                <!-- Chấm xanh lá cây chỉ thị trạng thái / thông báo ở viền trên vòng tròn ngoài -->
                {{-- <span
                    class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-neutral-900 dark:border-neutral-950 animate-pulse z-20"></span>
                --}}

                <!-- Nút tròn gradient chính bên trong (To gần full vòng tròn ngoài, chừa viền đen mỏng 3px) -->
                <a href="#"
                    class="w-[calc(100%-6px)] h-[calc(100%-6px)] rounded-full bg-gradient-to-tr from-primary via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-inner transition-all duration-300 hover:scale-105 active:scale-95 group"
                    aria-label="Tạo mới / Thêm hành động">
                    <!-- Icon dấu cộng chuyển động xoay nhẹ khi hover -->
                    <span
                        class="material-symbols-outlined text-[28px] sm:text-[32px] font-bold transition-transform duration-300 group-hover:rotate-90">
                        add
                    </span>
                </a>
            </div>
        </div>

        <!-- Tab 3: Hỗ trợ -->
        <a href="{{ $navItems[2]['route'] }}"
            class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[2]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span
                class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[2]['active'] ? 'scale-110' : '' }}">
                {{ $navItems[2]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[2]['name'] }}
            </span>
        </a>

        <!-- Tab 4: Hồ sơ -->
        <a href="{{ $navItems[3]['route'] }}"
            class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[3]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span
                class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[3]['active'] ? 'scale-110' : '' }}">
                {{ $navItems[3]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[3]['name'] }}
            </span>
        </a>

    </div>
</div>