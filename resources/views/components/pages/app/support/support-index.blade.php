<x-app-layout :title="__('Support - NDHGift')">
    {{-- ============================== --}}
    {{-- SECTION: Header trang liên hệ --}}
    {{-- ============================== --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-app-text">{{ __('Support') }}</h1>
            <p class="text-app-muted text-sm">{{ __('Get the help you need from our support team') }}</p>
        </div>
        <!-- Breadcrumbs chỉ hiển thị trên desktop -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
            <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                NDHGift
            </a>
            <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
            <span class="text-app-text">{{ __('Support') }}</span>
        </nav>
    </div>

    {{-- ============================== --}}
    {{-- SECTION: 2 cột chính --}}
    {{-- ============================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ========== CỘT TRÁI: Form gửi tin nhắn ========== --}}
        <div class="bg-app-surface border border-app-border rounded-2xl p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="size-10 rounded-xl bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-[22px]">edit_note</span>
                </div>
                <div>
                    <h2 class="text-base font-bold text-app-text">Gửi tin nhắn</h2>
                    <p class="text-xs text-app-muted">Điền thông tin bên dưới để liên hệ</p>
                </div>
            </div>

            {{-- Form liên hệ — trang tĩnh, chỉ hiển thị UI --}}
            <form class="flex flex-col gap-5" onsubmit="return false;">

                {{-- Họ và tên --}}
                <div>
                    <label for="contact-name"
                        class="block text-xs font-semibold text-app-muted uppercase tracking-wider mb-2">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-app-muted group-focus-within:text-primary transition-colors">person</span>
                        <input id="contact-name" type="text" placeholder="Nguyễn Văn A"
                            class="w-full pl-10 pr-4 py-3 text-sm bg-app-main border border-app-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-app-text placeholder:text-app-muted/50">
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="contact-email"
                        class="block text-xs font-semibold text-app-muted uppercase tracking-wider mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-app-muted group-focus-within:text-primary transition-colors">email</span>
                        <input id="contact-email" type="email" placeholder="email@example.com"
                            class="w-full pl-10 pr-4 py-3 text-sm bg-app-main border border-app-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-app-text placeholder:text-app-muted/50">
                    </div>
                </div>

                {{-- Chủ đề --}}
                <div>
                    <label for="contact-subject"
                        class="block text-xs font-semibold text-app-muted uppercase tracking-wider mb-2">
                        Chủ đề
                    </label>
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-lg text-app-muted group-focus-within:text-primary transition-colors">topic</span>
                        <select id="contact-subject"
                            class="w-full pl-10 pr-4 py-3 text-sm bg-app-main border border-app-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-app-text appearance-none cursor-pointer">
                            <option value="">-- Chọn chủ đề --</option>
                            <option value="general">Câu hỏi chung</option>
                            <option value="support">Hỗ trợ kỹ thuật</option>
                            <option value="billing">Thanh toán & Hoá đơn</option>
                            <option value="partnership">Hợp tác kinh doanh</option>
                            <option value="feedback">Góp ý & Phản hồi</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>

                {{-- Nội dung tin nhắn --}}
                <div>
                    <label for="contact-message"
                        class="block text-xs font-semibold text-app-muted uppercase tracking-wider mb-2">
                        Nội dung <span class="text-red-500">*</span>
                    </label>
                    <div class="relative group">
                        <span
                            class="material-symbols-outlined absolute left-3 top-3 text-lg text-app-muted group-focus-within:text-primary transition-colors">chat</span>
                        <textarea id="contact-message" rows="5" placeholder="Mô tả chi tiết vấn đề bạn cần hỗ trợ..."
                            class="w-full pl-10 pr-4 py-3 text-sm bg-app-main border border-app-border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all text-app-text placeholder:text-app-muted/50 resize-none"></textarea>
                    </div>
                </div>

                {{-- Nút gửi --}}
                <button type="submit"
                    class="w-full py-3 px-6 bg-primary hover:bg-primary/90 text-white text-sm font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">send</span>
                    Gửi tin nhắn
                </button>

                <p class="text-[11px] text-app-muted text-center leading-relaxed">
                    Bằng việc gửi tin nhắn, bạn đồng ý với
                    <a href="#" class="text-primary hover:underline font-medium">Chính sách bảo mật</a>
                    của chúng tôi.
                </p>
            </form>
        </div>

        {{-- ========== CỘT PHẢI: Thông tin liên hệ + Bản đồ ========== --}}
        <div class="flex flex-col gap-6">

            {{-- Card: Thông tin liên hệ --}}
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-emerald-500 text-[22px]">contact_phone</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-app-text">Thông tin liên hệ</h2>
                        <p class="text-xs text-app-muted">Liên lạc trực tiếp với chúng tôi</p>
                    </div>
                </div>

                <div class="flex flex-col gap-4">

                    {{-- Địa chỉ --}}
                    <div
                        class="flex items-start gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-primary/30 transition-colors group">
                        <div
                            class="size-10 rounded-xl bg-primary/10 flex items-center justify-center shrink-0 group-hover:bg-primary/20 transition-colors">
                            <span class="material-symbols-outlined text-primary text-[20px]">location_on</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-1">Địa chỉ</p>
                            <p class="text-sm font-bold text-app-text">Hà Nội, Việt Nam</p>
                        </div>
                    </div>

                    {{-- Hotline --}}
                    <a href="tel:+84388937608"
                        class="flex items-start gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-emerald-300 dark:hover:border-emerald-500/40 transition-colors group">
                        <div
                            class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center shrink-0 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/20 transition-colors">
                            <span class="material-symbols-outlined text-emerald-500 text-[20px]">call</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-1">Hotline</p>
                            <p class="text-sm font-bold text-app-text">+84 388 937 608</p>
                            <p class="text-xs text-app-muted mt-0.5">Hỗ trợ 24/7</p>
                        </div>
                    </a>

                    {{-- Email --}}
                    <a href="mailto:support@ndhshop.com"
                        class="flex items-start gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-blue-300 dark:hover:border-blue-500/40 transition-colors group">
                        <div
                            class="size-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0 group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition-colors">
                            <span class="material-symbols-outlined text-blue-500 text-[20px]">mail</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-1">Email</p>
                            <p class="text-sm font-bold text-app-text">support&#64;ndhshop.com</p>
                            <p class="text-xs text-app-muted mt-0.5">Phản hồi trong vòng 24h</p>
                        </div>
                    </a>

                    {{-- Giờ làm việc --}}
                    <div
                        class="flex items-start gap-4 p-4 rounded-xl bg-app-main border border-app-border hover:border-amber-300 dark:hover:border-amber-500/40 transition-colors group">
                        <div
                            class="size-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center shrink-0 group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20 transition-colors">
                            <span class="material-symbols-outlined text-amber-500 text-[20px]">schedule</span>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-app-muted uppercase tracking-wider mb-1">Giờ làm việc
                            </p>
                            <p class="text-sm font-bold text-app-text">Thứ 2 - Chủ nhật</p>
                            <p class="text-xs text-app-muted mt-0.5">08:00 - 22:00 (GMT+7)</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Card: Mạng xã hội --}}
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-5">
                    <div class="size-10 rounded-xl bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-violet-500 text-[22px]">share</span>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-app-text">Theo dõi chúng tôi</h2>
                        <p class="text-xs text-app-muted">Kết nối qua mạng xã hội</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    {{-- Facebook --}}
                    <a href="https://www.facebook.com/dichvuwebonline" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-3 p-3.5 rounded-xl bg-app-main border border-app-border hover:border-blue-400/50 hover:bg-blue-500/5 transition-all group">
                        <div
                            class="size-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-blue-500 text-[18px]">group</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-app-text truncate">Facebook</p>
                            <p class="text-[11px] text-app-muted truncate">Fanpage</p>
                        </div>
                    </a>

                    {{-- Zalo --}}
                    <a href="https://zalo.me/0388937608" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-3 p-3.5 rounded-xl bg-app-main border border-app-border hover:border-blue-400/50 hover:bg-blue-500/5 transition-all group">
                        <div
                            class="size-9 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-blue-500 text-[18px]">chat</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-app-text truncate">Zalo</p>
                            <p class="text-[11px] text-app-muted truncate">Chat trực tiếp</p>
                        </div>
                    </a>

                    {{-- Telegram --}}
                    <a href="#" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-3 p-3.5 rounded-xl bg-app-main border border-app-border hover:border-sky-400/50 hover:bg-sky-500/5 transition-all group">
                        <div
                            class="size-9 rounded-lg bg-sky-50 dark:bg-sky-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sky-500 text-[18px]">send</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-app-text truncate">Telegram</p>
                            <p class="text-[11px] text-app-muted truncate">Nhắn tin nhanh</p>
                        </div>
                    </a>

                    {{-- Beacons --}}
                    <a href="https://beacons.ai" target="_blank" rel="noopener noreferrer"
                        class="flex items-center gap-3 p-3.5 rounded-xl bg-app-main border border-app-border hover:border-violet-400/50 hover:bg-violet-500/5 transition-all group">
                        <div
                            class="size-9 rounded-lg bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-violet-500 text-[18px]">link</span>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-app-text truncate">Beacons</p>
                            <p class="text-[11px] text-app-muted truncate">Liên kết tổng hợp</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>