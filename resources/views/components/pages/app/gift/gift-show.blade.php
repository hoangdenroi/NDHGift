<x-app-layout :title="$giftTemplate->name . ' - NDHGift'">
    {{-- Tiêu đề trang & Breadcrumbs --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-app-border/40 pb-5">
        <div class="flex items-start gap-2.5">
            <!-- Nút quay lại (chỉ hiển thị trên mobile/tablet) -->
            <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}"
                class="inline-flex lg:hidden items-center justify-center p-1.5 rounded-xl text-app-text hover:bg-app-main/5 transition-colors active:scale-95"
                aria-label="{{ __('Quay lại') }}">
                <span class="material-symbols-outlined text-[24px] select-none">arrow_back</span>
            </a>

            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold text-app-text">{{ __('Chi tiết mẫu quà tặng') }}</h1>
                <p class="text-app-muted text-sm">{{ $giftTemplate->name }}</p>
            </div>
        </div>
        <!-- Breadcrumbs -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
            <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                NDHGift
            </a>
            <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
            <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                {{ __('Gift') }}
            </a>
            <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
            <span class="text-app-text">{{ __('Xem chi tiết') }}</span>
        </nav>
    </div>

    {{-- Layout chi tiết quà tặng --}}
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

        {{-- CỘT TRÁI: ẢNH PREVIEW & VIDEO --}}
        <div class="space-y-6">
            <div class="bg-app-surface border border-app-border rounded-3xl overflow-hidden shadow-lg group relative">
                <!-- Nhãn Hot Trend ở góc trên bên trái (nếu có is_hot) -->
                @if($giftTemplate->is_hot)
                    <span
                        class="absolute top-4 left-4 z-10 px-3 py-1 rounded-xl bg-red-600 text-xs font-black text-white uppercase tracking-wider select-none flex items-center gap-1 shadow-md">
                        <span class="material-symbols-outlined text-sm fill-current">local_fire_department</span>
                        <span>HOT</span>
                    </span>
                @endif

                <!-- Khung ảnh preview tỉ lệ đẹp -->
                <div class="relative w-full aspect-[4/3] bg-app-main/5 overflow-hidden">
                    <img src="{{ $imageUrl }}" alt="{{ $giftTemplate->name }}"
                        class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-700" />
                </div>
            </div>
        </div>

        {{-- CỘT PHẢI: THÔNG TIN CHI TIẾT & HÀNH ĐỘNG --}}
        <div class="space-y-6">
            <div class="bg-app-surface border border-app-border rounded-3xl p-6 sm:p-8 space-y-6 shadow-sm">

                {{-- Nhãn danh mục & Thống kê cơ bản --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-app-border/40 pb-4">
                    <span
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-xl bg-primary/10 text-primary text-xs font-bold uppercase tracking-wider">
                        <span
                            class="material-symbols-outlined text-sm select-none">{{ $giftTemplate->category?->icon ?? 'label' }}</span>
                        <span>{{ __($giftTemplate->category?->name ?? 'Mẫu quà tặng') }}</span>
                    </span>

                    <div class="flex items-center gap-4 text-xs text-app-muted">
                        <!-- Đánh giá sao -->
                        <div class="flex items-center gap-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <span
                                    class="material-symbols-outlined text-sm text-amber-500 fill-current select-none">star</span>
                            @endfor
                            <span class="font-bold text-app-text ml-1">{{ $giftTemplate->stars }}</span>
                        </div>
                        <!-- Số lượt bán -->
                        <div class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm select-none">shopping_bag</span>
                            <span>{{ __('Đã bán:') }} <strong
                                    class="text-app-text">{{ number_format($giftTemplate->sold) }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Tên & Mô tả --}}
                <div class="space-y-3">
                    <h2 class="text-2xl sm:text-3xl font-black text-app-text tracking-tight">{{ $giftTemplate->name }}
                    </h2>
                    <p class="text-sm text-app-muted leading-relaxed text-justify whitespace-pre-line">
                        {{ $giftTemplate->description }}
                    </p>
                </div>

                {{-- Khung Giá bán --}}
                <div
                    class="bg-app-main/5 border border-app-border/50 rounded-2xl p-4 flex items-center justify-between">
                    <div class="flex flex-col gap-0.5">
                        <span class="text-xs text-app-muted font-medium">{{ __('Giá thiết kế mẫu:') }}</span>
                        <div class="flex items-baseline gap-2.5">
                            <span class="text-2xl sm:text-3xl font-black text-rose-600">
                                {{ number_format($giftTemplate->price - ($giftTemplate->price * $giftTemplate->discount / 100)) }}đ
                            </span>
                            @if($giftTemplate->discount > 0)
                                <span class="text-sm text-app-muted line-through font-medium">
                                    {{ number_format($giftTemplate->price) }}đ
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($giftTemplate->discount > 0)
                        <span
                            class="px-3 py-1.5 rounded-xl bg-rose-500 text-white text-xs font-black select-none shadow-sm shadow-rose-500/10">
                            -{{ $giftTemplate->discount }}% OFF
                        </span>
                    @endif
                </div>

                {{-- Các đặc điểm nổi bật / Tính năng --}}
                <div class="space-y-3.5">
                    <h4 class="text-xs font-bold text-app-text uppercase tracking-wider">{{ __('Đặc điểm nổi bật:') }}
                    </h4>
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <li class="flex items-center gap-2 text-xs text-app-muted">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ __('Giao diện 3D trực quan, sinh động') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-app-muted">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ __('Hỗ trợ tải nhạc nền tùy chọn') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-app-muted">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ __('Tương thích mọi thiết bị di động') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-xs text-app-muted">
                            <span class="material-symbols-outlined text-green-500 text-lg">check_circle</span>
                            <span>{{ __('Tùy biến lời nhắn & hình ảnh gửi kèm') }}</span>
                        </li>
                    </ul>
                </div>

                {{-- Hộp Hành Động (Action) --}}
                <div class="pt-6 border-t border-app-border/40 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Nút Tạo Quà Tặng (Kiểm tra Auth) --}}
                    @auth
                        <a href="{{ $createUrl }}"
                            class="flex items-center justify-center gap-2 py-3.5 px-6 bg-primary hover:bg-primary/90 text-white rounded-2xl text-sm font-bold transition-all shadow-md shadow-primary/10 active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[20px] select-none">celebration</span>
                            <span>{{ __('Tạo quà ngay') }}</span>
                        </a>
                    @else
                        <a href="{{ route('login', ['locale' => app()->getLocale(), 'redirect' => $createUrl]) }}"
                            class="flex items-center justify-center gap-2 py-3.5 px-6 bg-primary hover:bg-primary/90 text-white rounded-2xl text-sm font-bold transition-all shadow-md shadow-primary/10 active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[20px] select-none">login</span>
                            <span>{{ __('Đăng nhập để tạo') }}</span>
                        </a>
                    @endauth

                    {{-- Nút Xem Demo --}}
                    @if($giftTemplate->demo_url && $giftTemplate->demo_url !== '#')
                        <a href="{{ $giftTemplate->demo_url }}" target="_blank"
                            class="flex items-center justify-center gap-2 py-3.5 px-6 bg-app-surface hover:bg-app-main/5 border border-app-border text-app-text rounded-2xl text-sm font-bold transition-all active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[20px] select-none">open_in_new</span>
                            <span>{{ __('Xem demo thực tế') }}</span>
                        </a>
                    @endif
                </div>

            </div>
        </div>

    </div>

</x-app-layout>