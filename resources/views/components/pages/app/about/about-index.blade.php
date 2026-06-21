<x-app-layout :title="__('About - NDHGift')">
    {{-- ============================== --}}
    {{-- SECTION: Header trang giới thiệu --}}
    {{-- ============================== --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-app-text">{{ __('About') }}</h1>
            <p class="text-app-muted text-sm">{{ __('Learn about our platform, mission, and core values') }}</p>
        </div>
        <!-- Breadcrumbs chỉ hiển thị trên desktop -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
            <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}" class="hover:text-primary transition-colors">
                NDHGift
            </a>
            <span class="material-symbols-outlined text-[14px] text-app-muted/40 select-none">chevron_right</span>
            <span class="text-app-text">{{ __('About') }}</span>
        </nav>
    </div>

    {{-- ============================== --}}
    {{-- SECTION: 2 cột chính --}}
    {{-- ============================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ========== CỘT TRÁI: Câu chuyện & Sứ mệnh (chiếm 2/3 cột) ========== --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            {{-- Card: Giới thiệu chung --}}
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[22px]">info</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-app-text">{{ __('Who are we?') }}</h2>
                        <p class="text-xs text-app-muted">{{ __('A brief introduction to NDHGift') }}</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm text-app-text/90 leading-relaxed">
                    <p>
                        <strong>NDHGift</strong> {{ __('is a leading online platform specializing in providing digital gift solutions, electronic card templates, discount codes, and unique customer gratitude programs. We bring users a rich library of gift templates, easily customizable to send messages of love on special occasions.') }}
                    </p>
                    <p>
                        {{ __('With the philosophy of putting user experience first, NDHGift constantly develops and updates new technologies to ensure high safety, security and the smoothest interactive interface for customers.') }}
                    </p>
                </div>
            </div>

            {{-- Card: Sứ mệnh & Giá trị cốt lõi --}}
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-500 text-[22px]">rocket_launch</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-app-text">{{ __('Mission & Vision') }}</h2>
                        <p class="text-xs text-app-muted">{{ __('Future development orientation') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Sứ mệnh --}}
                    <div class="p-4 rounded-xl bg-app-main border border-app-border hover:border-primary/30 transition-colors group">
                        <h3 class="text-sm font-bold text-app-text flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-primary text-lg transition-transform duration-300 group-hover:scale-110">flag</span>
                            {{ __('Mission') }}
                        </h3>
                        <p class="text-xs text-app-muted leading-relaxed">
                            {{ __('Shortening geographical distances and connecting souls with the most meaningful, delicate and creative spiritual gifts in the digital space.') }}
                        </p>
                    </div>

                    {{-- Tầm nhìn --}}
                    <div class="p-4 rounded-xl bg-app-main border border-app-border hover:border-emerald-500/30 transition-colors group">
                        <h3 class="text-sm font-bold text-app-text flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-emerald-500 text-lg transition-transform duration-300 group-hover:scale-110">visibility</span>
                            {{ __('Vision') }}
                        </h3>
                        <p class="text-xs text-app-muted leading-relaxed">
                            {{ __('Become the largest online gift ecosystem in Vietnam, where users can find all templates of cards and digital gifts for every event.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <h3 class="text-sm font-bold text-app-text">{{ __('Our Core Values') }}</h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-amber-300/30 transition-colors">
                            <span class="material-symbols-outlined text-amber-500 text-base">palette</span>
                            <span class="font-semibold text-app-text">{{ __('Constant Creativity') }}</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-blue-300/30 transition-colors">
                            <span class="material-symbols-outlined text-blue-500 text-base">security</span>
                            <span class="font-semibold text-app-text">{{ __('Absolute Security') }}</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-violet-300/30 transition-colors">
                            <span class="material-symbols-outlined text-violet-500 text-base">face</span>
                            <span class="font-semibold text-app-text">{{ __('Customer Centricity') }}</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-emerald-300/30 transition-colors">
                            <span class="material-symbols-outlined text-emerald-500 text-base">bolt</span>
                            <span class="font-semibold text-app-text">{{ __('Speed & Smoothness') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========== CỘT PHẢI: Con số thống kê & Kêu gọi hành động (1/3 cột) ========== --}}
        <div class="lg:col-span-1 flex flex-col gap-6">
            {{-- Card: Thống kê ấn tượng --}}
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="size-10 rounded-xl bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-violet-500 text-[22px]">leaderboard</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-app-text">{{ __('Impressive Numbers') }}</h2>
                        <p class="text-xs text-app-muted">{{ __('Achievements of NDHGift') }}</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- Stat 1 --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-primary/30 transition-colors group">
                        <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-0.5">{{ __('Members') }}</p>
                            <p class="text-lg font-black text-app-text">10,000+</p>
                        </div>
                    </div>

                    {{-- Stat 2 --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-emerald-300 dark:hover:border-emerald-500/40 transition-colors group">
                        <div class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-emerald-500 text-[20px]">featured_seasonal_and_gifts</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-0.5">{{ __('Gift Templates') }}</p>
                            <p class="text-lg font-black text-app-text">500+</p>
                        </div>
                    </div>

                    {{-- Stat 3 --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-blue-300 dark:hover:border-blue-500/40 transition-colors group">
                        <div class="size-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-blue-500 text-[20px]">handshake</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-0.5">{{ __('Transactions') }}</p>
                            <p class="text-lg font-black text-app-text">50,000+</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: CTA Khám phá ngay --}}
            <div class="bg-gradient-to-br from-primary via-indigo-600 to-purple-600 rounded-2xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg shadow-primary/20 flex flex-col gap-4">
                {{-- Background decor --}}
                <div class="absolute -right-10 -bottom-10 size-40 bg-white/10 rounded-full blur-2xl select-none pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 size-28 bg-white/10 rounded-full blur-xl select-none pointer-events-none"></div>

                <div class="relative z-10 space-y-2">
                    <h3 class="text-lg font-bold">{{ __('Explore the world of gifts') }}</h3>
                    <p class="text-xs text-white/80 leading-relaxed">
                        {{ __('Start choosing the most wonderful card template or receive attractive gifts from NDHGift today!') }}
                    </p>
                </div>

                <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}" 
                    class="relative z-10 w-full py-3 bg-white hover:bg-white/95 text-primary text-sm font-bold uppercase tracking-wider rounded-xl transition-all shadow-md active:scale-[0.98] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">explore</span>
                    {{ __('Get Started') }}
                </a>
            </div>
        </div>

    </div>
</x-app-layout>