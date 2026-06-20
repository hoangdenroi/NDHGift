<x-app-layout :title="__('Profile - NDHGift')">
    {{-- Khung bao ngoài của trang Profile --}}
    <div class="w-full flex flex-col gap-6 md:gap-8 max-w-5xl mx-auto py-4 md:py-8 px-4">
        
        {{-- Tiêu đề trang --}}
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                {{ __('Profile') }}
            </h1>
        </div>

        {{-- Layout chính: Tự động chia 2 phần trái-phải trên desktop, và xếp dọc trên mobile --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- PHẦN 1 (BÊN TRÁI): Thông tin cá nhân của User & Các chỉ số Thống kê --}}
            <div class="lg:col-span-5 flex flex-col items-center bg-white dark:bg-slate-800 p-6 md:p-8 rounded-3xl border border-slate-100 dark:border-slate-700/80 shadow-sm relative overflow-hidden">
                {{-- Decorative background blobs --}}
                <div class="absolute -right-12 -top-12 size-40 bg-primary/5 rounded-full blur-2xl"></div>
                <div class="absolute -left-12 -bottom-12 size-40 bg-blue-400/5 rounded-full blur-2xl"></div>

                {{-- Khung hiển thị Avatar --}}
                <div class="relative group cursor-pointer mb-5">
                    <div class="size-32 rounded-full overflow-hidden border-4 border-slate-50 dark:border-slate-700 shadow-md group-hover:scale-105 transition-transform duration-300">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->fullname ?? $user->username }}" class="w-full h-full object-cover">
                        @else
                            {{-- Avatar mặc định bằng ký tự đầu của tên --}}
                            <div class="w-full h-full bg-gradient-to-tr from-primary/20 to-blue-400/20 flex items-center justify-center text-primary dark:text-blue-400 font-bold text-3xl">
                                {{ strtoupper(substr($user->fullname ?? $user->username ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    
                    {{-- Nút Edit Avatar (Hiện tại là cứng) --}}
                    <button class="absolute bottom-1 right-1 size-9 bg-primary hover:bg-primary/90 text-white rounded-full flex items-center justify-center shadow-lg border-2 border-white dark:border-slate-800 transition-all hover:scale-110">
                        <span class="material-symbols-outlined !text-base">edit</span>
                    </button>
                </div>

                {{-- Tên người dùng --}}
                <h2 class="text-xl md:text-2xl font-bold text-slate-900 dark:text-white text-center tracking-tight mb-1">
                    {{ $user->fullname ?? $user->username }}
                </h2>
                
                {{-- Email người dùng --}}
                <p class="text-sm text-slate-500 dark:text-slate-400 text-center mb-8 font-medium">
                    {{ $user->email }}
                </p>

                {{-- Các con số thống kê (Stats) - Cứng theo yêu cầu của ảnh --}}
                <div class="grid grid-cols-3 gap-4 w-full pt-6 border-t border-slate-100 dark:border-slate-700/50">
                    {{-- Thống kê 1: Total time --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="size-10 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center mb-2">
                            <span class="text-lg">⏱️</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white leading-tight">2h 30m</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold mt-1 uppercase tracking-wider">{{ __('Total time') }}</span>
                    </div>

                    {{-- Thống kê 2: Burned --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="size-10 rounded-full bg-red-50 dark:bg-red-950/20 flex items-center justify-center mb-2">
                            <span class="text-lg">🔥</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white leading-tight">7200 cal</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold mt-1 uppercase tracking-wider">{{ __('Burned') }}</span>
                    </div>

                    {{-- Thống kê 3: Done --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="size-10 rounded-full bg-amber-50 dark:bg-amber-950/20 flex items-center justify-center mb-2">
                            <span class="text-lg">💪</span>
                        </div>
                        <span class="text-sm font-bold text-slate-900 dark:text-white leading-tight">2</span>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500 font-semibold mt-1 uppercase tracking-wider">{{ __('Done') }}</span>
                    </div>
                </div>
            </div>

            {{-- PHẦN 2 (BÊN PHẢI): Các mục Menu cài đặt --}}
            <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-3xl border border-slate-100 dark:border-slate-700/80 shadow-sm overflow-hidden">
                <div class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    
                    {{-- Menu Item 1: Personal --}}
                    <a href="#" class="group flex items-center justify-between p-5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                <span class="material-symbols-outlined !text-xl">person</span>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">
                                {{ __('Personal') }}
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-slate-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all !text-lg">
                            chevron_right
                        </span>
                    </a>

                    {{-- Menu Item 2: General --}}
                    <a href="#" class="group flex items-center justify-between p-5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                <span class="material-symbols-outlined !text-xl">tune</span>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">
                                {{ __('General') }}
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-slate-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all !text-lg">
                            chevron_right
                        </span>
                    </a>

                    {{-- Menu Item 3: Notification --}}
                    <a href="#" class="group flex items-center justify-between p-5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                <span class="material-symbols-outlined !text-xl">notifications</span>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">
                                {{ __('Notification') }}
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-slate-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all !text-lg">
                            chevron_right
                        </span>
                    </a>

                    {{-- Menu Item 4: Help --}}
                    <a href="#" class="group flex items-center justify-between p-5 hover:bg-slate-50 dark:hover:bg-slate-700/40 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="size-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center group-hover:bg-primary group-hover:text-white transition-all">
                                <span class="material-symbols-outlined !text-xl">help</span>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary transition-colors">
                                {{ __('Help') }}
                            </span>
                        </div>
                        <span class="material-symbols-outlined text-slate-300 group-hover:text-slate-500 group-hover:translate-x-1 transition-all !text-lg">
                            chevron_right
                        </span>
                    </a>
                    
                </div>
            </div>

        </div>
    </div>
</x-app-layout>