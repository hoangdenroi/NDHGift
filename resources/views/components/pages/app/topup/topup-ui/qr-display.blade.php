{{-- VIEW: Hiển thị QR --}}
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
                <div x-data="{ copied: false }" class="flex items-center justify-between gap-2 py-1">
                    <span class="font-bold text-app-text text-sm sm:text-base tracking-wider" x-text="qrDescription"></span>
                    <button @click="copyText(qrDescription); copied = true; setTimeout(() => copied = false, 2000)" 
                            :class="copied ? 'text-emerald-500 bg-emerald-500/10 hover:bg-emerald-500/20' : 'text-primary bg-primary/10 hover:bg-primary/20'"
                            class="p-1 rounded transition-colors">
                        <span class="material-symbols-outlined text-[16px]" x-text="copied ? 'check' : 'content_copy'"></span>
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
