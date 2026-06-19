@php
    // Kiểm tra route hiện tại để kích hoạt class active cho các tab
    $currentRoute = request()->route()->getName();
    $locale = app()->getLocale();

    // Định nghĩa danh sách các tab chính trên mobile navbar
    $navItems = [
        [
            'name' => 'Trang chủ',
            'route' => 'home',
            'icon' => 'home',
            'active' => request()->routeIs('home'),
        ],
        [
            'name' => 'Quà tặng',
            'route' => '#',
            'icon' => 'redeem',
            'active' => request()->routeIs('gifts*'),
        ],
        // Nút giữa (hình tròn to chứa dấu cộng) sẽ được render riêng
        [
            'name' => 'Hỗ trợ',
            'route' => '#',
            'icon' => 'forum',
            'active' => request()->routeIs('support*'),
        ],
        [
            'name' => 'Hồ sơ',
            'route' => '#',
            'icon' => 'person',
            'active' => request()->routeIs('profile*'),
        ],
    ];
@endphp

<!-- Bottom Navbar dành cho Mobile và Tablet (Ẩn trên Desktop) -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-app-surface/90 backdrop-blur-lg border-t border-app-border/80 shadow-[0_-4px_20px_-4px_rgba(0,0,0,0.08)] lg:hidden transition-all duration-300">
    <div class="relative max-w-md mx-auto px-4 h-16 sm:h-18 flex items-center justify-between">
        
        <!-- Tab 1: Trang chủ -->
        <a href="{{ route('home', ['locale' => $locale]) }}" 
           class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[0]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[0]['active'] ? 'scale-110 font-filled' : '' }}">
                {{ $navItems[0]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[0]['name'] }}
            </span>
        </a>

        <!-- Tab 2: Quà tặng -->
        <a href="#" 
           class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[1]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[1]['active'] ? 'scale-110' : '' }}">
                {{ $navItems[1]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[1]['name'] }}
            </span>
        </a>

        <!-- Cột giả ở giữa để giữ khoảng trống cho nút tròn to -->
        <div class="flex-1 flex justify-center items-center h-full relative">
            <!-- Nút tròn to nhô lên ở giữa chứa dấu cộng -->
            <a href="#" 
               class="absolute -top-5 sm:-top-6 w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-gradient-to-tr from-primary via-indigo-600 to-purple-600 flex items-center justify-center text-white shadow-lg shadow-primary/30 border-4 border-app-surface transition-all duration-300 hover:scale-105 active:scale-95 group"
               aria-label="Tạo mới / Thêm hành động">
                <!-- Chấm xanh lá cây chỉ thị trạng thái / thông báo ở viền trên nút tròn -->
                <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-emerald-500 rounded-full border-2 border-app-surface animate-pulse"></span>
                
                <!-- Icon dấu cộng chuyển động xoay nhẹ khi hover -->
                <span class="material-symbols-outlined text-[32px] sm:text-[36px] font-bold transition-transform duration-300 group-hover:rotate-90">
                    add
                </span>
            </a>
        </div>

        <!-- Tab 3: Hỗ trợ -->
        <a href="#" 
           class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[2]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[2]['active'] ? 'scale-110' : '' }}">
                {{ $navItems[2]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[2]['name'] }}
            </span>
        </a>

        <!-- Tab 4: Hồ sơ -->
        <a href="#" 
           class="flex flex-col items-center justify-center flex-1 py-2 text-center transition-all duration-200 active:scale-90 {{ $navItems[3]['active'] ? 'text-primary' : 'text-app-muted hover:text-app-text' }}">
            <span class="material-symbols-outlined text-[24px] transition-transform duration-200 {{ $navItems[3]['active'] ? 'scale-110' : '' }}">
                {{ $navItems[3]['icon'] }}
            </span>
            <span class="text-[10px] sm:text-xs mt-1 font-medium tracking-wide">
                {{ $navItems[3]['name'] }}
            </span>
        </a>

    </div>
</div>
