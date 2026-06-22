{{--
    Component hiển thị số dư và menu liên kết nhanh đến Nạp tiền & Hóa đơn.
    Đồng bộ phong cách thiết kế với component Ngôn ngữ (Language).
    Hỗ trợ cập nhật số dư realtime thông qua sự kiện WebSocket balance-updated.
    Tích hợp thêm tính năng quy đổi ngoại tệ VND sang USD ($) trực tuyến.
    Lấy tỷ giá thực tế từ API và tự động quy đổi hai chiều sau khi dừng nhập liệu 1 giây.
--}}

@auth
<div class="relative" x-data="{ 
    open: false,
    balance: {{ auth()->check() ? auth()->user()->balance : 0 }},
    showExchange: false,
    vndAmount: '',
    usdAmount: '',
    rate: 25400,
    loadingRate: false,
    timeoutId: null,

    // Gọi API để lấy tỷ giá USD -> VND mới nhất
    async fetchRate() {
        this.loadingRate = true;
        try {
            let response = await fetch('https://open.er-api.com/v6/latest/USD');
            let data = await response.json();
            if (data && data.rates && data.rates.VND) {
                this.rate = data.rates.VND;
            }
        } catch (error) {
            console.error('Lỗi khi lấy tỷ giá từ API:', error);
        } finally {
            this.loadingRate = false;
        }
    },

    // Chuyển đổi trạng thái hiển thị giao diện quy đổi
    async toggleExchange() {
        this.showExchange = !this.showExchange;
        this.vndAmount = '';
        this.usdAmount = '';
        if (this.timeoutId) clearTimeout(this.timeoutId);
        
        // Gọi API lấy tỷ giá khi vừa mở tab quy đổi
        if (this.showExchange) {
            await this.fetchRate();
        }
    },

    // Xử lý khi người dùng nhập số tiền VND
    handleVndInput(val) {
        this.vndAmount = val.replace(/[^0-9]/g, '');
        if (this.timeoutId) clearTimeout(this.timeoutId);
        
        if (!this.vndAmount) {
            this.usdAmount = '';
            return;
        }

        // Chờ 1 giây sau khi dừng gõ để gọi API và quy đổi
        this.timeoutId = setTimeout(async () => {
            await this.fetchRate();
            if (this.vndAmount) {
                this.usdAmount = (parseFloat(this.vndAmount) / this.rate).toFixed(2);
            }
        }, 1000);
    },

    // Xử lý khi người dùng nhập số tiền USD
    handleUsdInput(val) {
        this.usdAmount = val.replace(/[^0-9.]/g, '');
        if (this.timeoutId) clearTimeout(this.timeoutId);

        if (!this.usdAmount) {
            this.vndAmount = '';
            return;
        }

        // Chờ 1 giây sau khi dừng gõ để gọi API và quy đổi
        this.timeoutId = setTimeout(async () => {
            await this.fetchRate();
            if (this.usdAmount) {
                this.vndAmount = Math.round(parseFloat(this.usdAmount) * this.rate).toString();
            }
        }, 1000);
    }
}"
@balance-updated.window="balance = $event.detail.new_balance">
    
    {{-- Nút bấm ví tiền kích hoạt dropdown --}}
    <button @click="open = !open; showExchange = false; vndAmount = ''; usdAmount = '';"
        class="relative flex size-8 sm:size-10 cursor-pointer items-center justify-center rounded-full text-slate-900 dark:text-white transition-colors"
        :class="open ? 'bg-primary text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-800'">
        <span class="material-symbols-outlined text-[20px] sm:text-[24px]">account_balance_wallet</span>
    </button>

    {{-- Backdrop nền tối mờ khi hiển thị dropdown trên thiết bị di động --}}
    <div x-show="open" @click="open = false" x-cloak class="sm:hidden fixed inset-0 z-[199]"></div>

    {{-- Dropdown thông tin số dư và quy đổi tiền tệ --}}
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

        {{-- Thanh tiêu đề phía trên ngoài border chứa nút chuyển đổi Quy đổi --}}
        <div class="flex justify-between items-center mb-3">
            <span class="text-[11px] font-bold text-app-muted uppercase tracking-wider">{{ __('Wallet') }}</span>
            
            {{-- Nút bấm chuyển đổi giữa giao diện số dư và giao diện quy đổi VND ⇄ USD ($) --}}
            <button @click="toggleExchange" 
                class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold text-primary bg-primary/10 hover:bg-primary/20 transition-all cursor-pointer border border-primary/15">
                <span class="material-symbols-outlined text-[13px]" x-text="showExchange ? 'arrow_back' : 'currency_exchange'"></span>
                <span x-text="showExchange ? '{{ __('Back') }}' : 'VND ⇄ USD'"></span>
            </button>
        </div>

        {{-- 1. Giao diện xem Số dư & Chức năng nhanh (Mặc định) --}}
        <div x-show="!showExchange" 
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="border border-app-border rounded-xl p-4 bg-app-main/20 flex flex-col gap-4">
            
            {{-- Số dư to nhất ở giữa --}}
            <div class="flex flex-col items-center justify-center text-center py-2">
                <span class="text-[10px] sm:text-xs text-app-muted font-bold uppercase tracking-wider mb-1">{{ __('Current Balance') }}</span>
                <span class="text-2xl sm:text-3xl font-black text-app-text tracking-tight flex items-baseline gap-1">
                    <span x-text="new Intl.NumberFormat('vi-VN').format(balance)"></span>
                    <span class="text-[11px] sm:text-xs font-semibold text-app-muted">VNĐ</span>
                </span>
            </div>

            <div class="border-t border-app-border"></div>

            {{-- 2 nút Top Up & Hóa đơn liên kết nhanh --}}
            <div class="grid grid-cols-2 gap-3">
                {{-- Nút Nạp tiền --}}
                <a href="{{ route('app.topup') }}" @click="open = false"
                    class="flex flex-col items-center justify-center p-3 rounded-xl bg-app-surface hover:bg-primary/10 border border-app-border hover:border-primary/30 text-app-text hover:text-primary transition-all text-center gap-1.5 group">
                    <span class="material-symbols-outlined text-[20px] text-amber-500 group-hover:scale-110 transition-transform">add_card</span>
                    <span class="text-[12px] font-bold">{{ __('Top Up') }}</span>
                </a>
                
                {{-- Nút Hóa đơn --}}
                <a href="{{ route('app.billing') }}" @click="open = false"
                    class="flex flex-col items-center justify-center p-3 rounded-xl bg-app-surface hover:bg-primary/10 border border-app-border hover:border-primary/30 text-app-text hover:text-primary transition-all text-center gap-1.5 group">
                    <span class="material-symbols-outlined text-[20px] text-slate-700 dark:text-slate-300 group-hover:scale-110 transition-transform">receipt_long</span>
                    <span class="text-[12px] font-bold">{{ __('Billing') }}</span>
                </a>
            </div>
        </div>

        {{-- 2. Giao diện form quy đổi ngoại tệ VND sang USD --}}
        <div x-show="showExchange" 
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="border border-app-border rounded-xl p-4 bg-app-main/20 flex flex-col gap-4" x-cloak>
            
            {{-- Tiêu đề và tỷ giá tham khảo --}}
            <div class="text-center pb-1">
                <span class="text-xs text-app-text font-bold block mb-0.5">Quy đổi ngoại tệ</span>
                <span class="text-[10px] text-primary font-semibold flex items-center justify-center gap-1">
                    <span>Tỷ giá: 1 USD ($) ≈</span>
                    <span x-text="loadingRate ? '...' : new Intl.NumberFormat('vi-VN').format(rate)"></span>
                    <span>VND</span>
                </span>
            </div>

            {{-- Form nhập số liệu quy đổi hai chiều --}}
            <div class="flex flex-col gap-3">
                {{-- Trường nhập VND --}}
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-app-muted">Số tiền VND</label>
                    <div class="relative">
                        <input type="text" :value="vndAmount" @input="handleVndInput($event.target.value)"
                            class="w-full h-9 px-3 pr-12 rounded-lg border border-app-border bg-app-surface text-app-text text-xs focus:border-primary focus:ring-primary outline-none"
                            placeholder="Nhập VNĐ" />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-bold text-app-muted">VND</span>
                    </div>
                </div>

                {{-- Biểu tượng hoán đổi --}}
                <div class="flex justify-center -my-1">
                    <span class="material-symbols-outlined text-app-muted text-[16px]" :class="loadingRate ? 'animate-spin' : ''">swap_vert</span>
                </div>

                {{-- Trường nhập USD ($) --}}
                <div class="flex flex-col gap-1">
                    <label class="text-[10px] font-bold text-app-muted">Số tiền USD ($)</label>
                    <div class="relative">
                        <input type="text" :value="usdAmount" @input="handleUsdInput($event.target.value)"
                            class="w-full h-9 px-3 pr-12 rounded-lg border border-app-border bg-app-surface text-app-text text-xs focus:border-primary focus:ring-primary outline-none"
                            placeholder="Nhập USD ($)" />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-bold text-app-muted">USD ($)</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-app-border pt-2.5">
                {{-- Hiển thị giá trị quy đổi nhanh của số dư hiện tại trong ví --}}
                <div class="flex justify-between items-center text-[10px] text-app-muted">
                    <span>Quy đổi số dư ví:</span>
                    <span class="font-bold text-app-text" x-text="new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(balance / rate)"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endauth
