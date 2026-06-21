<x-app-layout :title="__('About - NDHGift')">
    {{-- ============================== --}}
    {{-- SECTION: Header trang giới thiệu --}}
    {{-- ============================== --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-app-text">{{ __('About') }}</h1>
            <p class="text-app-muted text-sm">Tìm hiểu về nền tảng, sứ mệnh và giá trị cốt lõi của chúng tôi</p>
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
                        <h2 class="text-base font-bold text-app-text">Chúng tôi là ai?</h2>
                        <p class="text-xs text-app-muted">Giới thiệu ngắn gọn về NDHGift</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm text-app-text/90 leading-relaxed">
                    <p>
                        <strong>NDHGift</strong> là nền tảng trực tuyến hàng đầu chuyên cung cấp các giải pháp quà tặng số, template thiệp điện tử, mã giảm giá và các chương trình tri ân khách hàng độc đáo. Chúng tôi mang đến cho người dùng một thư viện mẫu quà tặng phong phú, dễ dàng tùy biến để gửi trao những thông điệp yêu thương vào các dịp đặc biệt.
                    </p>
                    <p>
                        Với triết lý đặt trải nghiệm người dùng lên hàng đầu, NDHGift không ngừng phát triển và cập nhật các công nghệ mới nhằm đảm bảo tính an toàn, bảo mật cao và giao diện tương tác mượt mà nhất cho khách hàng.
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
                        <h2 class="text-base font-bold text-app-text">Sứ mệnh & Tầm nhìn</h2>
                        <p class="text-xs text-app-muted">Định hướng phát triển tương lai</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Sứ mệnh --}}
                    <div class="p-4 rounded-xl bg-app-main border border-app-border hover:border-primary/30 transition-colors group">
                        <h3 class="text-sm font-bold text-app-text flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-primary text-lg transition-transform duration-300 group-hover:scale-110">flag</span>
                            Sứ mệnh
                        </h3>
                        <p class="text-xs text-app-muted leading-relaxed">
                            Rút ngắn khoảng cách địa lý và kết nối những tâm hồn bằng những món quà tinh thần ý nghĩa, tinh tế và sáng tạo nhất trên không gian số.
                        </p>
                    </div>

                    {{-- Tầm nhìn --}}
                    <div class="p-4 rounded-xl bg-app-main border border-app-border hover:border-emerald-500/30 transition-colors group">
                        <h3 class="text-sm font-bold text-app-text flex items-center gap-2 mb-2">
                            <span class="material-symbols-outlined text-emerald-500 text-lg transition-transform duration-300 group-hover:scale-110">visibility</span>
                            Tầm nhìn
                        </h3>
                        <p class="text-xs text-app-muted leading-relaxed">
                            Trở thành hệ sinh thái quà tặng trực tuyến lớn nhất Việt Nam, nơi người dùng có thể tìm thấy mọi mẫu thiệp và quà tặng số cho mọi sự kiện.
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-3">
                    <h3 class="text-sm font-bold text-app-text">Giá trị cốt lõi của chúng tôi</h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-amber-300/30 transition-colors">
                            <span class="material-symbols-outlined text-amber-500 text-base">palette</span>
                            <span class="font-semibold text-app-text">Sáng tạo không ngừng</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-blue-300/30 transition-colors">
                            <span class="material-symbols-outlined text-blue-500 text-base">security</span>
                            <span class="font-semibold text-app-text">Bảo mật tuyệt đối</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-violet-300/30 transition-colors">
                            <span class="material-symbols-outlined text-violet-500 text-base">face</span>
                            <span class="font-semibold text-app-text">Khách hàng là trọng tâm</span>
                        </div>
                        <div class="flex items-center gap-2 p-2.5 rounded-lg bg-app-main border border-app-border hover:border-emerald-300/30 transition-colors">
                            <span class="material-symbols-outlined text-emerald-500 text-base">bolt</span>
                            <span class="font-semibold text-app-text">Tốc độ & Mượt mà</span>
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
                        <h2 class="text-base font-bold text-app-text">Con số ấn tượng</h2>
                        <p class="text-xs text-app-muted">Thành tựu đạt được của NDHGift</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- Stat 1 --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-primary/30 transition-colors group">
                        <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-primary text-[20px]">groups</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-0.5">Thành viên</p>
                            <p class="text-lg font-black text-app-text">10,000+</p>
                        </div>
                    </div>

                    {{-- Stat 2 --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-emerald-300 dark:hover:border-emerald-500/40 transition-colors group">
                        <div class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-emerald-500 text-[20px]">featured_seasonal_and_gifts</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-0.5">Mẫu quà tặng</p>
                            <p class="text-lg font-black text-app-text">500+</p>
                        </div>
                    </div>

                    {{-- Stat 3 --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-blue-300 dark:hover:border-blue-500/40 transition-colors group">
                        <div class="size-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-blue-500 text-[20px]">handshake</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-0.5">Giao dịch</p>
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
                    <h3 class="text-lg font-bold">Khám phá thế giới quà tặng</h3>
                    <p class="text-xs text-white/80 leading-relaxed">
                        Hãy bắt đầu chọn một mẫu thiệp tuyệt vời nhất hoặc nhận những phần quà hấp dẫn từ NDHGift ngay hôm nay!
                    </p>
                </div>

                <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}" 
                    class="relative z-10 w-full py-3 bg-white hover:bg-white/95 text-primary text-sm font-bold uppercase tracking-wider rounded-xl transition-all shadow-md active:scale-[0.98] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">explore</span>
                    Bắt đầu ngay
                </a>
            </div>
        </div>

    </div>
</x-app-layout>