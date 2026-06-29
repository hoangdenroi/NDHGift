<aside :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': !sidebarOpen,
        'lg:translate-x-0': true,
        'lg:w-[4.5rem]': sidebarMini,
        'w-64': !sidebarMini
    }"
    class="-translate-x-full lg:translate-x-0 w-64 flex-shrink-0 flex flex-col bg-white dark:bg-[#0b0f17] border-r border-slate-200 dark:border-border-dark fixed lg:relative inset-y-0 left-0 z-40 transition-all duration-300 ease-in-out"
    x-cloak>
    <div class="p-6 flex items-center gap-3 overflow-hidden h-[72px]" :class="sidebarMini ? 'justify-center px-0' : ''">
        <div class="bg-primary/20 p-2 rounded-lg text-primary flex-shrink-0 flex items-center justify-center">
            <img src="{{ asset('NDHGift.jpg') }}" alt="Logo" class="w-8 h-8"
                style="object-fit: cover; border-radius: 8px;">
        </div>
        <div x-show="!sidebarMini" class="whitespace-nowrap transition-all duration-300">
            <h1 class="text-slate-900 dark:text-white text-base font-bold leading-tight">NDHGift</h1>
            <p class="text-slate-500 text-xs font-medium">Phiên bản: 1.0.0</p>
        </div>
    </div>
    @php
        // Class cho wrapper item (Background + Padding)
        $itemWrapperFull = 'rounded-lg px-3 py-2.5 w-full mx-auto';
        $itemWrapperMini = 'h-[3rem] flex items-center justify-center w-full';

        // Class inner text/icon (Colors)
        $innerActive = 'text-primary dark:text-primary font-medium';
        $innerDefault = 'text-slate-500 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-white font-medium';
    @endphp

    <div class="py-2 flex flex-col flex-1 pb-10 custom-scrollbar"
        :class="sidebarMini ? 'overflow-visible px-0 gap-0' : 'overflow-y-auto overflow-x-hidden px-3 gap-1'">
        <div x-show="!sidebarMini"
            class="text-xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-2 mt-2 whitespace-nowrap">Menu
            Chính
        </div>
        <div x-show="sidebarMini" class="w-10 h-px bg-slate-200 dark:bg-border-dark my-2 mx-auto"></div>

        <!-- Dashboard -->
        <a class="group relative block focus:outline-none" href="{{ route('admin.dashboard') }}">
            <div class="{{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 dark:bg-[#1e2430]' : 'hover:bg-slate-100 dark:hover:bg-[#1e2430]' }} transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ request()->routeIs('admin.dashboard') ? $innerActive : $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">dashboard</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Dashboard</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Dashboard
            </div>
        </a>

        <!-- Quản lý người dùng -->
        <a class="group relative block focus:outline-none" href="{{ route('admin.users.index') }}">
            <div class="{{ request()->routeIs('admin.users.*') ? 'bg-primary/10 dark:bg-[#1e2430]' : 'hover:bg-slate-100 dark:hover:bg-[#1e2430]' }} transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ request()->routeIs('admin.users.*') ? $innerActive : $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">person</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Quản lý người dùng</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Quản lý người dùng
            </div>
        </a>

        <!-- Quản lý mã giảm giá -->
        <a class="group relative block focus:outline-none" href="{{ route('admin.coupons.index') }}">
            <div class="{{ request()->routeIs('admin.coupons.*') ? 'bg-primary/10 dark:bg-[#1e2430]' : 'hover:bg-slate-100 dark:hover:bg-[#1e2430]' }} transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ request()->routeIs('admin.coupons.*') ? $innerActive : $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">confirmation_number</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Quản lý mã giảm giá</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Quản lý mã giảm giá
            </div>
        </a>

        <!-- Quản lý thông báo -->
        <a class="group relative block focus:outline-none" href="{{ route('admin.notifications.index') }}">
            <div class="{{ request()->routeIs('admin.notifications.*') ? 'bg-primary/10 dark:bg-[#1e2430]' : 'hover:bg-slate-100 dark:hover:bg-[#1e2430]' }} transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ request()->routeIs('admin.notifications.*') ? $innerActive : $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">notifications</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Quản lý thông báo</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Quản lý thông báo
            </div>
        </a>

        <!-- Quản lý danh mục quà tặng -->
        <a class="group relative block focus:outline-none" href="{{ route('admin.gift-categories.index') }}">
            <div class="{{ request()->routeIs('admin.gift-categories.*') ? 'bg-primary/10 dark:bg-[#1e2430]' : 'hover:bg-slate-100 dark:hover:bg-[#1e2430]' }} transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ request()->routeIs('admin.gift-categories.*') ? $innerActive : $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">category</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Quản lý danh mục</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Quản lý danh mục
            </div>
        </a>

        <!-- Quản lý mẫu quà tặng -->
        <a class="group relative block focus:outline-none" href="{{ route('admin.gift-templates.index') }}">
            <div class="{{ request()->routeIs('admin.gift-templates.*') ? 'bg-primary/10 dark:bg-[#1e2430]' : 'hover:bg-slate-100 dark:hover:bg-[#1e2430]' }} transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ request()->routeIs('admin.gift-templates.*') ? $innerActive : $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">redeem</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Quản lý mẫu quà tặng</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Quản lý mẫu quà tặng
            </div>
        </a>

        <!-- Quản lý giao dịch -->
        <a class="group relative block focus:outline-none" href="#">
            <div class="hover:bg-slate-100 dark:hover:bg-[#1e2430] transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">payments</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Quản lý giao dịch</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Quản lý giao dịch
            </div>
        </a>

        <div x-show="!sidebarMini"
            class="text-xs font-bold text-slate-500 uppercase tracking-wider px-3 mb-2 mt-6 whitespace-nowrap">Hệ Thống
        </div>
        <div x-show="sidebarMini" class="w-10 h-px bg-slate-200 dark:bg-border-dark my-2 mx-auto mt-6"></div>

        <!-- Cấu hình hệ thống -->
        <a class="group relative block focus:outline-none" href="#">
            <div class="hover:bg-slate-100 dark:hover:bg-[#1e2430] transition-all overflow-hidden"
                :class="sidebarMini ? '{{$itemWrapperMini}}' : '{{$itemWrapperFull}}'">
                <div class="flex items-center transition-colors {{ $innerDefault }}"
                    :class="sidebarMini ? 'justify-center w-full' : 'gap-3 w-full'">
                    <span class="material-symbols-outlined text-[20px] flex-shrink-0">settings</span>
                    <span x-show="!sidebarMini" class="text-sm whitespace-nowrap">Cấu hình hệ thống</span>
                </div>
            </div>

            <div x-show="sidebarMini"
                class="absolute left-full top-0 hidden group-hover:flex items-center h-[3rem] min-w-[220px] px-5 bg-slate-100 dark:bg-[#1e2430] text-slate-900 dark:text-white text-sm font-bold shadow-lg shadow-black/10 dark:shadow-none border-y border-r border-slate-200 dark:border-border-dark z-50 whitespace-nowrap rounded-r-lg uppercase tracking-wide">
                Cấu hình hệ thống
            </div>
        </a>
    </div>
</aside>