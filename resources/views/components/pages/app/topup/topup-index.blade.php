<x-app-layout :title="__('Top Up') . ' - NDHGift'">

    <div class="flex flex-col gap-6"
        @balance-updated.window="balance = $event.detail.new_balance; fetchPending();"
        @topup-status-changed.window="handleTopupStatusChanged($event.detail)"
        x-data="topupIndex()">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-xl sm:text-2xl font-bold text-app-text">{{ __('Top Up') }}</h1>
                <p class="text-app-muted text-xs sm:text-sm">{{ __('Top up your account to use services') }}</p>
            </div>
            <button
                @click="if(topupView === 'history') { topupView = 'form'; } else { topupView = 'history'; if(!historyLoaded) fetchHistory(1); }"
                x-show="topupView !== 'qr_display'"
                class="flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-primary bg-primary/10 hover:bg-primary/20 rounded-xl transition-colors">
                <span class="material-symbols-outlined text-[18px]"
                    x-text="topupView === 'form' ? 'history' : 'arrow_back'"></span>
                <span x-text="topupView === 'form' ? '{{ __('Top Up History') }}' : '{{ __('Back') }}'"></span>
            </button>
        </div>

        {{-- ============================== --}}
        {{-- VIEW: Form nạp tiền & GD Chờ --}}
        {{-- ============================== --}}
        <div x-show="topupView === 'form'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            {{-- Form nạp tiền --}}
            @include('components.pages.app.topup.topup-ui.form')

            {{-- Bảng giao dịch đang chờ --}}
            @include('components.pages.app.topup.topup-ui.pending-transactions')

        </div>

        {{-- ============================== --}}
        {{-- VIEW: Hiển thị QR --}}
        {{-- ============================== --}}
        @include('components.pages.app.topup.topup-ui.qr-display')

        {{-- ============================== --}}
        {{-- VIEW: Lịch sử nạp tiền --}}
        {{-- ============================== --}}
        @include('components.pages.app.topup.topup-ui.history')

    </div>

@push('scripts')
<script>
    window.topupConfig = {
        balance: {{ auth()->user()->balance ?? 0 }},
        pendingTransactions: @js($pendingTransactions ?? [])
    };

    window.topupIndex = function () {
        return {
            balance: window.topupConfig.balance,
            pendingTransactions: window.topupConfig.pendingTransactions,
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
            historyLoaded: false,
            txToCancel: null,

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
                        this.historyLoaded = true;
                    }
                } catch (error) {
                    console.error('Lỗi khi tải lịch sử nạp tiền:', error);
                } finally {
                    this.isLoadingHistory = false;
                }
            },

            selectPaymentMethod(method) {
                this.paymentMethod = method;
            },

            async generateQR() {
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

            confirmCancel(tx) {
                this.txToCancel = tx;
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-cancel-topup' }));
            },

            async executeCancelTx() {
                if (!this.txToCancel) return;
                const id = this.txToCancel.id;
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
                } finally {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-cancel-topup' }));
                    this.txToCancel = null;
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
        };
    };
</script>
@endpush
</x-app-layout>