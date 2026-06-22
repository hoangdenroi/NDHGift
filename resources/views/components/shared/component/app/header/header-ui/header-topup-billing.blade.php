{{--
    Component hiển thị số dư và menu liên kết nhanh đến Nạp tiền & Hóa đơn.
    Đồng bộ phong cách thiết kế với component Ngôn ngữ (Language).
    Hỗ trợ cập nhật số dư realtime thông qua sự kiện WebSocket balance-updated.
--}}

@auth
<div class="relative" x-data="{ 
    open: false,
    balance: {{ auth()->check() ? auth()->user()->balance : 0 }}
}"
@balance-updated.window="balance = $event.detail.new_balance">
    
    {{-- Nút bấm ví tiền kích hoạt dropdown --}}
    <button @click="open = !open"
        class="relative flex size-8 sm:size-10 cursor-pointer items-center justify-center rounded-full text-slate-900 dark:text-white transition-colors"
        :class="open ? 'bg-primary text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-800'">
        <span class="material-symbols-outlined text-[20px] sm:text-[24px]">account_balance_wallet</span>
    </button>

    {{-- Backdrop nền tối mờ khi hiển thị dropdown trên thiết bị di động --}}
    <div x-show="open" @click="open = false" x-cloak class="sm:hidden fixed inset-0 z-[199]"></div>

    {{-- Dropdown thông tin số dư --}}
    <div x-show="open" @click.outside="open = false" @keydown.escape.window="open = false"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-[56px] right-3 w-72 sm:absolute sm:top-[100%] sm:right-0 sm:mt-2 sm:w-80
               bg-app-surface rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] border border-app-border p-5 z-[200] origin-top-right" x-cloak>

        {{-- Mũi tên nhỏ trỏ vào icon nút ví tiền (tự động điều chỉnh vị trí) --}}
        <div x-ref="arrowOuter" x-effect="
                if(open) {
                    $nextTick(() => {
                        let btn = $root.querySelector('button');
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

        {{-- Nội dung hiển thị số dư to ở giữa dropdown --}}
        <div class="flex flex-col items-center justify-center text-center py-4">
            <span class="text-xs text-app-muted font-bold uppercase tracking-wider mb-1">{{ __('Current Balance') }}</span>
            <span class="text-2xl sm:text-3xl font-black text-app-text tracking-tight flex items-baseline gap-1">
                <span x-text="new Intl.NumberFormat('vi-VN').format(balance)"></span>
                <span class="text-xs sm:text-sm font-semibold text-app-muted">VNĐ</span>
            </span>
        </div>

        {{-- Các liên kết chuyển hướng nhanh đến trang Nạp tiền và Hóa đơn --}}
        <div class="grid grid-cols-2 gap-3 mt-4 border-t border-app-border pt-4">
            {{-- Liên kết đến trang Nạp tiền (Topup) --}}
            <a href="{{ route('app.topup') }}" @click="open = false"
                class="flex flex-col items-center justify-center p-3 rounded-xl bg-primary/10 hover:bg-primary/20 border border-primary/20 text-primary font-bold transition-all text-center gap-1 group">
                <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">add_card</span>
                <span class="text-[12px] font-semibold">{{ __('Top Up') }}</span>
            </a>
            
            {{-- Liên kết đến trang Hóa đơn / Lịch sử tài chính (Billing) --}}
            <a href="{{ route('app.billing') }}" @click="open = false"
                class="flex flex-col items-center justify-center p-3 rounded-xl bg-app-main hover:bg-app-border border border-app-border text-app-text font-bold transition-all text-center gap-1 group">
                <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">receipt_long</span>
                <span class="text-[12px] font-semibold">{{ __('Billing') }}</span>
            </a>
        </div>
    </div>
</div>
@endauth
