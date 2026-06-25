<x-app-layout :title="__('Top Up') . ' - NDHGift'">

    <div class="flex flex-col gap-6"
        @balance-updated.window="balance = $event.detail.new_balance; fetchPending();"
        @topup-status-changed.window="handleTopupStatusChanged($event.detail)"
        x-data="{
            balance: {{ auth()->user()->balance ?? 0 }},
            amount: '',
            paymentMethod: 'qr',
            topupView: 'form',
            qrUrl: '',
            qrDescription: '',
            activeQrTx: null,
            isLoading: false,
            transactions: [],
            currentPage: 1,
            lastPage: 1,
            isLoadingHistory: false,
            pendingTransactions: @json($pendingTransactions ?? []),

            init() {
                // Khởi tạo bộ đếm thời gian cho các giao dịch đang chờ
                setInterval(() => {
                    this.updateCountdowns();
                }, 1000);
                this.updateCountdowns();
            },

            updateCountdowns() {
                this.pendingTransactions.forEach(tx => {
                    if (!tx.expires_at) return;
                    const expires = new Date(tx.expires_at).getTime();
                    const now = new Date().getTime();
                    const diff = expires - now;

                    if (diff <= 0) {
                        tx.timeLeft = 'Đã hết hạn';
                        if (tx.status === 'PENDING') {
                            tx.status = 'EXPIRED';
                            // Cập nhật lại danh sách sau khi giao dịch hết hạn
                            setTimeout(() => { this.fetchPending(); }, 2000);
                        }
                    } else {
                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        tx.timeLeft = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    }
                });
            },

            async fetchPending() {
                try {
                    const response = await fetch('/api/v1/topup/pending', {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.pendingTransactions = data.data;
                        this.updateCountdowns();
                    }
                } catch (error) {
                    console.error('Lỗi khi tải danh sách giao dịch chờ:', error);
                }
            },

            handleTopupStatusChanged(detail) {
                // Tìm kiếm giao dịch trong danh sách để cập nhật trạng thái realtime
                const tx = this.pendingTransactions.find(t => t.id === detail.transaction_id);
                if (tx) {
                    tx.status = detail.new_status;
                    if (detail.new_status === 'SUCCESS') {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: 'Nạp tiền thành công', message: `Giao dịch ${tx.payment_code} đã được xử lý!` }
                        }));
                        // Flash hiệu ứng thành công rồi tải lại danh sách
                        setTimeout(() => {
                            this.fetchPending();
                            if (this.activeQrTx && this.activeQrTx.id === tx.id) {
                                this.activeQrTx = null;
                                this.topupView = 'form';
                            }
                        }, 3000);
                    } else {
                        this.fetchPending();
                        if (this.activeQrTx && this.activeQrTx.id === tx.id) {
                            this.activeQrTx = null;
                            this.topupView = 'form';
                        }
                    }
                } else {
                    this.fetchPending();
                }
            },

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
                        detail: { type: 'warning', title: '{{ __('Not Logged In') }}', message: '{{ __('Please log in to select a payment method!') }}' }
                    }));
                    return;
                @endguest
                this.paymentMethod = method;
            },

            async generateQR() {
                @guest
                    console.warn('[Topup] Blocked: User tried to generate QR code without being logged in.');
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'warning', title: '{{ __('Not Logged In') }}', message: '{{ __('Please log in to top up!') }}' }
                    }));
                    return;
                @endguest

                if (!this.amount || parseInt(this.amount) < 20000) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'warning', title: '{{ __('Warning') }}', message: '{{ __('Please enter a valid amount (minimum 20,000đ)') }}' }
                    }));
                    return;
                }
                if (this.paymentMethod !== 'qr') {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'info', title: '{{ __('Notification') }}', message: '{{ __('This method is being updated!') }}' }
                    }));
                    return;
                }

                // Kiểm tra giới hạn 3 giao dịch PENDING ở phía Client
                const activePendingCount = this.pendingTransactions.filter(t => t.status === 'PENDING').length;
                if (activePendingCount >= 3) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'warning', title: 'Giới hạn giao dịch', message: 'Bạn đang có 3 giao dịch chờ thanh toán. Vui lòng hoàn tất hoặc hủy bớt giao dịch cũ!' }
                    }));
                    return;
                }

                this.isLoading = true;
                try {
                    const response = await fetch('/api/v1/topup/create', {
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
                        this.fetchPending();
                        this.showQr(data.transaction);
                        this.amount = '';
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: 'Thành công', message: 'Đã tạo giao dịch nạp tiền. Vui lòng quét mã QR thanh toán!' }
                        }));
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', title: '{{ __('Error') }}', message: data.message || '{{ __('An error occurred while generating the payment QR!') }}' }
                        }));
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', title: '{{ __('Error') }}', message: '{{ __('Server connection error. Please try again!') }}' }
                    }));
                } finally {
                    this.isLoading = false;
                }
            },

            showQr(tx) {
                const bankId = '{{ config('payment.vietqr.bin', '970423') }}';
                const accountNo = '{{ config('payment.vietqr.account', '10003179213') }}';
                const accountName = '{{ config('payment.vietqr.name', 'NGUYEN DUC HOANG') }}';
                const template = '{{ config('payment.vietqr.template', 'compact') }}';
                const prefix = '{{ config('payment.vietqr.prefix', 'SEVQR ') }}';

                const description = `${prefix.trim()} ${tx.payment_code}`;

                this.qrUrl = `https://img.vietqr.io/image/${bankId}-${accountNo}-${template}.png?amount=${tx.amount}&addInfo=${encodeURIComponent(description)}&accountName=${encodeURIComponent(accountName)}`;
                this.qrDescription = description;
                this.activeQrTx = tx;
                this.topupView = 'qr_display';
            },

            async cancelTx(id) {
                if (!confirm('Bạn có chắc chắn muốn hủy giao dịch nạp tiền đang chờ này không?')) {
                    return;
                }

                try {
                    const response = await fetch(`/api/v1/topup/${id}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    });
                    const data = await response.json();
                    if (data.success) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: 'Hủy thành công', message: data.message }
                        }));
                        this.fetchPending();
                        if (this.activeQrTx && this.activeQrTx.id === id) {
                            this.activeQrTx = null;
                            this.topupView = 'form';
                        }
                    } else {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'error', title: 'Lỗi', message: data.message }
                        }));
                    }
                } catch (error) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'error', title: 'Lỗi kết nối', message: 'Không thể hủy giao dịch. Vui lòng thử lại sau.' }
                    }));
                }
            },

            copyText(text) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: 'Đã sao chép', message: 'Nội dung chuyển khoản đã được sao chép vào bộ nhớ tạm.' }
                        }));
                    });
                } else {
                    // Fallback
                    const el = document.createElement('textarea');
                    el.value = text;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: { type: 'success', title: 'Đã sao chép', message: 'Nội dung chuyển khoản đã được sao chép vào bộ nhớ tạm.' }
                    }));
                }
            }
        }">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-xl sm:text-2xl font-bold text-app-text">{{ __('Top Up') }}</h1>
                <p class="text-app-muted text-xs sm:text-sm">{{ __('Top up your account to use services') }}</p>
            </div>
            <button
                @click="if(topupView === 'history') { topupView = 'form'; } else { topupView = 'history'; fetchHistory(1); }"
                x-show="topupView !== 'qr_display'"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-primary bg-primary/10 hover:bg-primary/20 rounded-xl transition-colors">
                <span class="material-symbols-outlined text-[18px]"
                    x-text="topupView === 'form' ? 'history' : 'arrow_back'"></span>
                <span x-text="topupView === 'form' ? '{{ __('Top Up History') }}' : '{{ __('Back') }}'"></span>
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
                            <p class="text-xs text-app-muted font-medium uppercase tracking-wider">{{ __('Current Balance') }}</p>
                            <p class="text-xl sm:text-2xl font-bold text-app-text">
                                <span x-text="new Intl.NumberFormat('vi-VN').format(balance)"></span>
                                <span class="text-xs sm:text-sm font-medium text-app-muted">VNĐ</span>
                            </p>
                        </div>
                    </div>

                    {{-- Card nhập số tiền --}}
                    <div class="bg-app-surface border border-app-border rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-app-border">
                            <h2 class="text-sm sm:text-base font-bold text-app-text">{{ __('Amount to Top Up') }}</h2>
                        </div>
                        <div class="p-6 space-y-5">
                            {{-- Input nhập số tiền --}}
                            <div class="relative">
                                <input x-model="amount"
                                    @input="amount = $event.target.value.replace(/[^0-9]/g, ''); if(parseInt(amount) > 100000000) amount = '100000000'"
                                    class="w-full h-14 px-4 pr-16 rounded-xl border-2 border-primary/40 bg-app-main text-app-text placeholder:text-app-muted text-sm sm:text-base focus:border-primary focus:ring-primary transition-colors"
                                    type="text" inputmode="numeric" :placeholder="'{{ __('From: 20,000đ — Max: 100,000,000đ') }}'" />
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
                                <label class="block text-xs sm:text-sm font-medium text-app-muted">{{ __('Quick Select') }}</label>
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
                            <h2 class="text-sm sm:text-base font-bold text-app-text">{{ __('Payment Method') }}</h2>
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
                                    <p class="text-xs text-app-muted mt-0.5 truncate">{{ __('Scan bank QR code') }}</p>
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
                                    <p class="text-xs text-app-muted mt-0.5 truncate">{{ __('QR, ATM, Visa') }}</p>
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
                    <button @click="generateQR()" :disabled="isLoading || pendingTransactions.filter(t => t.status === 'PENDING').length >= 3"
                        class="w-full flex items-center justify-center h-12 bg-primary hover:bg-primary/90 text-white font-bold rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span class="material-symbols-outlined text-[20px]" x-show="!isLoading">add_card</span>
                        <span class="material-symbols-outlined text-[20px] animate-spin" x-show="isLoading"
                            x-cloak>autorenew</span>
                        <span x-text="pendingTransactions.filter(t => t.status === 'PENDING').length >= 3 ? 'Giới hạn 3 giao dịch chờ' : (isLoading ? '{{ __('Generating QR...') }}' : '{{ __('Top Up') }}')"></span>
                    </button>
                </div>

            </div>

            {{-- BẢNG GIAO DỊCH ĐANG CHỜ --}}
            <div x-show="pendingTransactions.length > 0" class="mt-8 bg-app-surface border border-app-border rounded-xl overflow-hidden" x-transition x-cloak>
                <div class="px-6 py-4 border-b border-app-border flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full bg-primary animate-pulse"></span>
                        <h2 class="text-sm sm:text-base font-bold text-app-text">Giao dịch nạp tiền đang chờ thanh toán</h2>
                    </div>
                    <span class="text-xs font-semibold text-app-muted" x-text="`${pendingTransactions.filter(t => t.status === 'PENDING').length}/3 giao dịch`"></span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <template x-for="tx in pendingTransactions" :key="tx.id">
                            <div :class="{
                                    'border-primary/30 bg-primary/5': tx.status === 'PENDING',
                                    'border-emerald-500/30 bg-emerald-500/5': tx.status === 'SUCCESS',
                                    'border-red-500/30 bg-red-500/5': tx.status === 'FAILED' || tx.status === 'CANCELLED' || tx.status === 'EXPIRED',
                                 }"
                                 class="relative flex flex-col p-4 rounded-xl border transition-all duration-300 overflow-hidden">

                                <!-- Chi tiết giao dịch -->
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <span class="text-[10px] text-app-muted font-medium uppercase">Mã GD</span>
                                        <p class="text-sm font-bold text-app-text uppercase" x-text="tx.payment_code"></p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-app-muted font-medium uppercase">Số tiền</span>
                                        <p class="text-sm font-bold text-primary" x-text="new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                                    </div>
                                </div>

                                <!-- Nội dung CK -->
                                <div class="bg-app-main p-2.5 rounded-lg border border-app-border flex items-center justify-between mb-4">
                                    <div class="min-w-0 flex-1 pr-2">
                                        <span class="text-[9px] text-app-muted block uppercase font-bold tracking-wider">Nội dung chuyển khoản</span>
                                        <span class="text-xs font-mono font-bold text-app-text select-all truncate block" x-text="tx.order_info"></span>
                                    </div>
                                    <button @click="copyText(tx.order_info)" class="p-1.5 rounded bg-primary/10 hover:bg-primary/20 text-primary transition-colors shrink-0" title="Sao chép">
                                        <span class="material-symbols-outlined text-[16px]">content_copy</span>
                                    </button>
                                </div>

                                <!-- Trạng thái & Action -->
                                <div class="flex items-center justify-between mt-auto pt-2.5 border-t border-app-border/40">
                                    <div class="flex items-center gap-1.5">
                                        <template x-if="tx.status === 'PENDING'">
                                            <div class="flex items-center gap-1 text-amber-500 font-bold text-xs">
                                                <span class="material-symbols-outlined text-[14px] animate-spin">schedule</span>
                                                <span x-text="tx.timeLeft || '60:00'"></span>
                                            </div>
                                        </template>
                                        <template x-if="tx.status === 'SUCCESS'">
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-500">
                                                <span class="material-symbols-outlined text-[14px]">check_circle</span> Thành công
                                            </span>
                                        </template>
                                        <template x-if="tx.status === 'EXPIRED'">
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-app-muted">
                                                <span class="material-symbols-outlined text-[14px]">error</span> Hết hạn
                                            </span>
                                        </template>
                                        <template x-if="tx.status === 'CANCELLED'">
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-red-500">
                                                <span class="material-symbols-outlined text-[14px]">cancel</span> Đã hủy
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Thao tác -->
                                    <div class="flex items-center gap-1.5">
                                        <template x-if="tx.status === 'PENDING'">
                                            <div class="flex items-center gap-1.5">
                                                <button @click="showQr(tx)" class="px-2.5 py-1 rounded bg-primary text-white text-[11px] font-bold hover:bg-primary/90 transition-colors flex items-center gap-1">
                                                    <span class="material-symbols-outlined text-[14px]">qr_code</span> Xem QR
                                                </button>
                                                <button @click="cancelTx(tx.id)" class="p-1 rounded border border-red-500/20 hover:bg-red-500/10 text-red-500 transition-colors" title="Hủy giao dịch">
                                                    <span class="material-symbols-outlined text-[15px]">close</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>
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
                        {{ __('Open your banking app and scan the QR code above. All information is auto-filled.') }}
                    </p>
                </div>

                {{-- Chi tiết thanh toán --}}
                <div class="w-full bg-app-surface border border-app-border rounded-xl p-5 space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-app-border">
                        <span class="text-xs sm:text-sm text-app-muted">{{ __('Top Up Amount:') }}</span>
                        <span class="font-bold text-primary text-base sm:text-lg">
                            <span x-text="activeQrTx ? new Intl.NumberFormat('vi-VN').format(activeQrTx.amount) : '0'"></span>đ
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 pb-3 border-b border-app-border">
                        <span class="text-xs sm:text-sm text-app-muted">{{ __('Transfer Content:') }}</span>
                        <div class="flex items-center justify-between gap-2 py-1">
                            <span class="font-bold text-app-text text-sm sm:text-base tracking-wider" x-text="qrDescription"></span>
                            <button @click="copyText(qrDescription)" class="p-1 rounded bg-primary/10 hover:bg-primary/20 text-primary transition-colors">
                                <span class="material-symbols-outlined text-[16px]">content_copy</span>
                            </button>
                        </div>
                    </div>
                    <div class="pt-1 flex items-start justify-center gap-1.5 px-2">
                        <span class="material-symbols-outlined text-[18px] text-amber-500 mt-0.5 shrink-0">info</span>
                        <span
                            class="text-[11px] sm:text-[13px] leading-snug text-amber-600 dark:text-amber-500 font-medium text-center">
                            {{ __('Please enter') }} <span class="underline underline-offset-2">{{ __('exact transfer content') }}</span>
                            {{ __('for the system to automatically credit your account in a moment.') }}
                        </span>
                    </div>
                </div>

                {{-- Nút hành động --}}
                <div class="w-full flex flex-col gap-3">
                    <button @click="topupView = 'form'"
                        class="w-full flex items-center justify-center h-11 bg-primary hover:bg-primary/90 text-white text-sm sm:text-base font-bold rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] gap-2">
                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                        Tôi đã chuyển khoản xong
                    </button>
                    <button @click="topupView = 'form'"
                        class="w-full flex items-center justify-center h-11 border border-app-border hover:bg-app-main text-app-text text-sm sm:text-base font-bold rounded-xl transition-colors gap-2">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                        {{ __('Back') }}
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
                <p class="text-app-muted text-sm">{{ __('No top up transactions yet.') }}</p>
                <button @click="topupView = 'form'" class="mt-4 text-primary text-sm font-semibold hover:underline">
                    {{ __('Top up now →') }}
                </button>
            </div>

            {{-- Danh sách giao dịch --}}
            <div x-show="transactions.length > 0"
                class="bg-app-surface border border-app-border rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-app-border">
                    <h2 class="text-sm sm:text-base font-bold text-app-text">{{ __('Transaction History') }}</h2>
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
                                        x-text="item.status === 'SUCCESS' ? '{{ __('Success') }}' : '{{ __('Failed') }}'">
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
                        <span x-text="isLoadingHistory ? '{{ __('Loading...') }}' : '{{ __('Load More History') }}'"></span>
                    </button>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>