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
                        @input="amount = $event.target.value.replace(/[^0-9]/g, ''); if(amount && parseInt(amount) > 100000000) amount = '100000000'"
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
