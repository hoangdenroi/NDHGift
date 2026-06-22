<x-app-layout title="Nạp tiền - NDHShop">

    <div class="flex flex-col gap-6"
        @balance-updated.window="balance = $event.detail.new_balance; if (topupView === 'qr_display') { topupView = 'form'; amount = ''; }"
        x-data="{
            balance: {{ auth()->user()->balance ?? 0 }},
            amount: '',
            paymentMethod: 'qr',
            topupView: 'form',
            qrUrl: '',
            qrDescription: '',
            lastGeneratedAmount: '',
            isLoading: false,
            transactions: [],
            currentPage: 1,
            lastPage: 1,
            isLoadingHistory: false,
            async fetchHistory(page = 1) {
                @guest
                    console.warn('[Topup] Blocked: Guest user tried to fetch top-up history.');
                    return;
                @endguest

                if (this.isLoadingHistory) return;
                this.isLoadingHistory = true;
                try {
                    const response = await fetch(`/api/v1/topup/history?page=${page}`, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.success) {
                        if (page === 1) {
                            this.transactions = data.data.data;
                        } else {
                            this.transactions = [...this.transactions, ...data.data.data];
                        }
                        this.currentPage = data.data.current_page;
                        this.lastPage = data.data.last_page;
                    }
                } catch (error) {
                    console.error('Lỗi khi tải lịch sử nạp tiền:', error);
                } finally {
                    this.isLoadingHistory = false;
                }
            },
            selectPaymentMethod(method) {
                @guest
                    console.warn('[Topup] Blocked: User tried to select payment method without being logged in.');
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'warning', title: 'Chưa đăng nhập', message: 'Vui lòng đăng nhập để chọn phương thức thanh toán!' }
                    }));
                    return;
                @endguest
                this.paymentMethod = method;
            },
            async generateQR() {
                @guest
                    console.warn('[Topup] Blocked: User tried to generate QR code without being logged in.');
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'warning', title: 'Chưa đăng nhập', message: 'Vui lòng đăng nhập để nạp tiền!' }
                    }));
                    return;
                @endguest

                if (!this.amount || parseInt(this.amount) < 20000) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'warning', title: 'Cảnh báo', message: 'Vui lòng nhập số tiền hợp lệ (tối thiểu 20,000đ)' }
                    }));
                    return;
                }
                if (this.paymentMethod !== 'qr') {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'info', title: 'Thông báo', message: 'Phương thức này đang được cập nhật!' }
                    }));
                    return;
                }

                // Tối ưu: Nếu số tiền không đổi và đã có QR thì không gọi lại API
                if (this.amount == this.lastGeneratedAmount && this.qrUrl) {
                    this.topupView = 'qr_display';
                    return;
                }

                this.isLoading = true;
                try {
                    const response = await fetch('/api/v1/topup/qrcode', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        },
                        body: JSON.stringify({ amount: this.amount })
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.qrUrl = data.qr_url;
                        this.qrDescription = data.description;
                        this.lastGeneratedAmount = this.amount; // Lưu lại số tiền đã tạo QR thành công
                        this.topupView = 'qr_display';
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', title: 'Lỗi', message: data.message || 'Có lỗi xảy ra khi tạo QR thanh toán!' }
                        }));
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', title: 'Lỗi', message: 'Lỗi kết nối đến máy chủ. Vui lòng thử lại!' }
                    }));
                } finally {
                    this.isLoading = false;
                }
            }
        }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-xl sm:text-2xl font-bold text-app-text">Nạp tiền</h1>
                <p class="text-app-muted text-xs sm:text-sm">Nạp tiền vào tài khoản để sử dụng dịch vụ</p>
            </div>
            <button
                @click="if(topupView === 'history') { topupView = 'form'; } else { topupView = 'history'; fetchHistory(1); }"
                x-show="topupView !== 'qr_display'"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-primary bg-primary/10 hover:bg-primary/20 rounded-xl transition-colors">
                <span class="material-symbols-outlined text-[18px]"
                    x-text="topupView === 'form' ? 'history' : 'arrow_back'"></span>
                <span x-text="topupView === 'form' ? 'Lịch sử nạp' : 'Quay lại'"></span>
            </button>
        </div>

        {{-- ============================== --}}
        {{-- VIEW: Form nạp tiền --}}
        {{-- ============================== --}}
        <div x-show="topupView === 'form'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Cột trái: Form nhập tiền --}}
                <div class="lg:col-span-2 flex flex-col gap-6">

                    {{-- Số dư hiện tại --}}
                    <div
                        class="bg-gradient-to-r from-primary/10 to-primary/5 dark:from-primary/20 dark:to-transparent rounded-xl p-5 flex items-center gap-4 border border-primary/20">
                        <div class="size-12 rounded-full bg-primary/20 flex items-center justify-center">
                            <span
                                class="material-symbols-outlined text-primary text-[28px]">account_balance_wallet</span>
                        </div>
                        <div>
                            <p class="text-xs text-app-muted font-medium uppercase tracking-wider">Số dư hiện tại</p>
                            <p class="text-xl sm:text-2xl font-bold text-app-text">
                                <span x-text="new Intl.NumberFormat('vi-VN').format(balance)"></span>
                                <span class="text-xs sm:text-sm font-medium text-app-muted">VNĐ</span>
                            </p>
                        </div>
                    </div>

                    {{-- Card nhập số tiền --}}
                    <div class="bg-app-surface border border-app-border rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-app-border">
                            <h2 class="text-sm sm:text-base font-bold text-app-text">Số tiền muốn nạp</h2>
                        </div>
                        <div class="p-6 space-y-5">
                            {{-- Input nhập số tiền --}}
                            <div class="relative">
                                <input x-model="amount"
                                    @input="amount = $event.target.value.replace(/[^0-9]/g, ''); if(parseInt(amount) > 100000000) amount = '100000000'"
                                    class="w-full h-14 px-4 pr-16 rounded-xl border-2 border-primary/40 bg-app-main text-app-text placeholder:text-app-muted text-sm sm:text-base focus:border-primary focus:ring-primary transition-colors"
                                    type="text" inputmode="numeric" placeholder="Từ: 20,000đ — Tối đa: 100,000,000đ" />
                                <div class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                                    <button x-show="amount" @click="amount = ''" type="button"
                                        class="size-6 rounded-full bg-app-border hover:bg-app-muted/30 flex items-center justify-center transition-colors">
                                        <span class="material-symbols-outlined text-app-muted text-[14px]">close</span>
                                    </button>
                                    <span class="text-app-muted font-semibold">đ</span>
                                </div>
                            </div>

                            {{-- Chọn nhanh --}}
                            <div class="space-y-2">
                                <label class="block text-xs sm:text-sm font-medium text-app-muted">Chọn nhanh</label>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    @foreach([20000, 50000, 100000, 200000, 500000, 1000000, 2000000, 5000000] as $val)
                                        <button @click="amount = {{ $val }}"
                                            :class="amount == {{ $val }} ? 'border-primary bg-primary/10 text-primary' : 'border-app-border text-app-text'"
                                            class="py-3 rounded-xl border text-center font-semibold text-xs sm:text-sm hover:border-primary transition-all">
                                            {{ number_format($val, 0, ',', ',') }}đ
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Cột phải: Phương thức thanh toán --}}
                <div class="lg:col-span-1 flex flex-col gap-6">
                    <div class="bg-app-surface border border-app-border rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-app-border">
                            <h2 class="text-sm sm:text-base font-bold text-app-text">Phương thức thanh toán</h2>
                        </div>
                        <div class="p-6 space-y-3">
                            {{-- QR Code --}}
                            <button @click="selectPaymentMethod('qr')"
                                :class="paymentMethod === 'qr' ? 'border-primary ring-2 ring-primary' : 'border-app-border'"
                                class="relative flex items-center gap-4 w-full p-4 pr-12 rounded-xl border bg-app-main hover:border-primary transition-all text-left">
                                <img src="{{ asset('assets/images/qr-code.jpg') }}" alt="QR Code"
                                    class="w-10 h-10 shrink-0 object-contain rounded">
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-app-text truncate">QR Code</p>
                                    <p class="text-xs text-app-muted mt-0.5 truncate">Quét mã QR ngân hàng</p>
                                </div>
                                <div x-show="paymentMethod === 'qr'"
                                    class="absolute top-1/2 -translate-y-1/2 right-4 size-5 rounded-full bg-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-white text-[14px]">check</span>
                                </div>
                            </button>

                            {{-- VNPay --}}
                            <button @click="selectPaymentMethod('vnpay')"
                                :class="paymentMethod === 'vnpay' ? 'border-primary ring-2 ring-primary' : 'border-app-border'"
                                class="relative flex items-center gap-4 w-full p-4 pr-12 rounded-xl border bg-app-main hover:border-primary transition-all text-left">
                                <img src="{{ asset('assets/images/VNPay-Logo.jpg') }}" alt="VNPay"
                                    class="w-10 h-10 shrink-0 object-contain rounded">
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-app-text truncate">VNPay</p>
                                    <p class="text-xs text-app-muted mt-0.5 truncate">QR, ATM, Visa</p>
                                </div>
                                <div x-show="paymentMethod === 'vnpay'"
                                    class="absolute top-1/2 -translate-y-1/2 right-4 size-5 rounded-full bg-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-white text-[14px]">check</span>
                                </div>
                            </button>

                            {{-- Paypal --}}
                            <button @click="selectPaymentMethod('paypal')"
                                :class="paymentMethod === 'paypal' ? 'border-primary ring-2 ring-primary' : 'border-app-border'"
                                class="relative flex items-center gap-4 w-full p-4 pr-12 rounded-xl border bg-app-main hover:border-primary transition-all text-left">
                                <img src="{{ asset('assets/images/paypal-logo.png') }}" alt="PayPal"
                                    class="w-10 h-10 shrink-0 object-contain">
                                <div class="min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-app-text truncate">PayPal</p>
                                    <p class="text-xs text-app-muted mt-0.5 truncate">PayPal</p>
                                </div>
                                <div x-show="paymentMethod === 'paypal'"
                                    class="absolute top-1/2 -translate-y-1/2 right-4 size-5 rounded-full bg-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-white text-[14px]">check</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    {{-- Nút nạp tiền --}}
                    <button @click="generateQR()" :disabled="isLoading"
                        class="w-full flex items-center justify-center h-12 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] gap-2 disabled:opacity-75 disabled:cursor-wait">
                        <span class="material-symbols-outlined text-[20px]" x-show="!isLoading">add_card</span>
                        <span class="material-symbols-outlined text-[20px] animate-spin" x-show="isLoading"
                            x-cloak>autorenew</span>
                        <span x-text="isLoading ? 'Đang tạo QR...' : 'Nạp tiền'"></span>
                    </button>
                </div>

            </div>
        </div>

        {{-- ============================== --}}
        {{-- VIEW: Hiển thị QR --}}
        {{-- ============================== --}}
        <div x-show="topupView === 'qr_display'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>

            <div class="max-w-md mx-auto flex flex-col gap-5">

                {{-- Ảnh QR --}}
                <div
                    class="w-full bg-app-surface border border-app-border rounded-2xl p-6 flex flex-col items-center gap-4">
                    <div class="bg-white p-4 rounded-xl shadow-sm border border-app-border">
                        <img :src="qrUrl" alt="VietQR" class="w-64 h-64 object-contain" />
                    </div>
                    <p class="text-xs sm:text-sm text-app-muted max-w-xs text-center px-2">
                        Mở ứng dụng ngân hàng và quét mã bên trên. Mọi thông tin đã được điền tự động.
                    </p>
                </div>

                {{-- Chi tiết thanh toán --}}
                <div class="w-full bg-app-surface border border-app-border rounded-xl p-5 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-app-border">
                        <span class="text-xs sm:text-sm text-app-muted">Số tiền nạp:</span>
                        <span class="font-bold text-primary text-base sm:text-lg">
                            <span x-text="new Intl.NumberFormat('vi-VN').format(amount)"></span>đ
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 pb-3 border-b border-app-border">
                        <span class="text-xs sm:text-sm text-app-muted">Nội dung chuyển khoản:</span>
                        <span class="font-bold text-app-text text-sm sm:text-base text-center tracking-wider py-1"
                            x-text="qrDescription"></span>
                    </div>
                    <div class="pt-1 flex items-start justify-center gap-1.5 px-2">
                        <span class="material-symbols-outlined text-[18px] text-amber-500 mt-0.5 shrink-0">info</span>
                        <span
                            class="text-[11px] sm:text-[13px] leading-snug text-amber-600 dark:text-amber-500 font-medium text-center">
                            Vui lòng nhập <span class="underline underline-offset-2">đúng nội dung chuyển khoản</span>
                            để hệ thống tự động cộng tiền cho bạn trong giây lát.
                        </span>
                    </div>
                </div>

                {{-- Nút hành động --}}
                <div class="w-full flex flex-col gap-3">
                    <button @click="window.location.href = '{{ route('app.topup') }}'"
                        class="w-full flex items-center justify-center h-11 bg-primary hover:bg-primary/90 text-white text-sm sm:text-base font-bold rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] gap-2">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        Đã nạp tiền
                    </button>
                    <button @click="topupView = 'form'"
                        class="w-full flex items-center justify-center h-11 border border-app-border hover:bg-app-main text-app-text text-sm sm:text-base font-bold rounded-xl transition-colors gap-2">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        Trở lại
                    </button>
                </div>

            </div>
        </div>

        {{-- ============================== --}}
        {{-- VIEW: Lịch sử nạp tiền --}}
        {{-- ============================== --}}
        <div x-show="topupView === 'history'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>

            {{-- Trạng thái trống --}}
            <div x-show="transactions.length === 0 && !isLoadingHistory"
                class="bg-app-surface border border-app-border rounded-xl p-12 flex flex-col items-center text-center">
                <span class="material-symbols-outlined text-[48px] text-app-muted/40 mb-3">receipt_long</span>
                <p class="text-app-muted text-sm">Chưa có giao dịch nạp tiền nào.</p>
                <button @click="topupView = 'form'" class="mt-4 text-primary text-sm font-semibold hover:underline">Nạp
                    tiền ngay →</button>
            </div>

            {{-- Danh sách giao dịch --}}
            <div x-show="transactions.length > 0"
                class="bg-app-surface border border-app-border rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-app-border">
                    <h2 class="text-sm sm:text-base font-bold text-app-text">Lịch sử giao dịch</h2>
                </div>
                <div class="p-6 flex flex-col items-center">
                    <div class="space-y-3 w-full">
                        <template x-for="item in transactions" :key="item.id">
                            <div
                                class="flex items-center justify-between p-4 gap-3 rounded-xl border border-app-border bg-app-main hover:border-primary/30 transition-colors overflow-hidden">
                                <div class="flex items-center gap-3 shrink-0">
                                    <div class="size-10 rounded-full flex items-center justify-center shrink-0"
                                        :class="item.status === 'SUCCESS' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500'">
                                        <span class="material-symbols-outlined text-[20px]"
                                            x-text="item.status === 'SUCCESS' ? 'check_circle' : 'close'"></span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm sm:text-base font-bold text-app-text truncate"
                                            x-text="(item.status === 'SUCCESS' ? '+' : '') + new Intl.NumberFormat('vi-VN').format(item.amount) + 'đ'">
                                        </p>
                                        <p class="text-xs text-app-muted mt-0.5 truncate"
                                            x-text="new Date(item.created_at).toLocaleString('vi-VN')"></p>
                                    </div>
                                </div>
                                <div class="text-right min-w-0 flex-1">
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded text-[11px] font-bold uppercase tracking-wider shrink-0"
                                        :class="item.status === 'SUCCESS' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500'"
                                        x-text="item.status === 'SUCCESS' ? 'Thành công' : 'Thất bại'">
                                    </span>
                                    <div class="w-full mt-1 flex justify-end">
                                        <p class="text-xs text-app-muted truncate max-w-full inline-block"
                                            :title="item.order_info" x-text="item.order_info || item.payment_method">
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Nút tải thêm --}}
                    <button x-show="currentPage < lastPage" @click="fetchHistory(currentPage + 1)"
                        :disabled="isLoadingHistory"
                        class="mt-6 flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-medium text-primary bg-primary/10 hover:bg-primary/20 rounded-full transition-colors disabled:opacity-50">
                        <span x-show="isLoadingHistory" class="material-symbols-outlined text-[18px] animate-spin"
                            x-cloak>autorenew</span>
                        <span x-text="isLoadingHistory ? 'Đang tải...' : 'Tải thêm lịch sử'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>