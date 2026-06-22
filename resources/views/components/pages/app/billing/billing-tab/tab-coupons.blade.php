<div class="grid grid-cols-1 lg:grid-cols-12 gap-6" 
     x-data="{ 
        couponInput: '',
        isLoading: false,
        async redeemCoupon() {
            if (!this.couponInput.trim()) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: '{{ __('Failed') }}',
                        message: '{{ __('Please enter the gift code before redeeming!') }}'
                    }
                }));
                return;
            }

            this.isLoading = true;
            try {
                const response = await fetch('{{ route('app.coupon.redeem') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ code: this.couponInput })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'success',
                            title: '{{ __('Success') }}',
                            message: data.message
                        }
                    }));
                    this.couponInput = '';
                    
                    // Reload trang sau khi toast hiển thị để cập nhật số dư & bảng giao dịch
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    window.dispatchEvent(new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: '{{ __('Redemption Failed') }}',
                            message: data.message || '{{ __('Invalid or already used gift code.') }}'
                        }
                    }));
                }
            } catch (error) {
                console.error('Error redeeming coupon:', error);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: '{{ __('Connection Error') }}',
                        message: '{{ __('Cannot connect to server. Please check your connection.') }}'
                    }
                }));
            } finally {
                this.isLoading = false;
            }
        }
     }">
    {{-- Form nhập mã quy đổi (Cột trái) --}}
    <div class="lg:col-span-5 flex flex-col gap-4">
        <h3 class="text-xs sm:text-sm font-bold text-app-text flex items-center gap-1.5">
            <span class="material-symbols-outlined text-primary text-lg">local_offer</span>
            {{ __('Enter Gift Code') }}
        </h3>
        <div class="flex items-center gap-3">
            <input type="text" :placeholder="'{{ __('ENTER GIFT CODE HERE') }}'" x-model="couponInput" @keydown.enter="redeemCoupon()" :disabled="isLoading"
                class="flex-1 bg-app-surface border border-app-border text-app-text text-xs sm:text-sm rounded-xl focus:ring-1 focus:ring-primary focus:border-primary block px-4 py-2.5 outline-none font-mono uppercase tracking-wider shadow-inner disabled:opacity-60 disabled:cursor-not-allowed">
            <button type="button" @click="redeemCoupon()" :disabled="isLoading"
                class="px-5 py-2.5 bg-primary hover:bg-primary/90 disabled:opacity-60 disabled:cursor-not-allowed text-white text-xs sm:text-sm font-bold rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98] shrink-0 flex items-center gap-1.5">
                <span x-show="isLoading" class="animate-spin border-2 border-white/30 border-t-white rounded-full size-3.5 inline-block"></span>
                <span x-text="isLoading ? '{{ __('Processing...') }}' : '{{ __('Redeem') }}'"></span>
            </button>
        </div>
        
        <div class="p-4 bg-app-surface border border-app-border rounded-xl shadow-sm">
            <h4 class="text-xs font-bold text-app-text mb-2 flex items-center gap-1">
                <span class="material-symbols-outlined text-base text-app-muted">info</span> {{ __('Redemption Notes') }}
            </h4>
            <ul class="text-[10px] sm:text-xs text-app-muted space-y-2 list-disc list-inside pl-1 font-medium">
                <li>{{ __('Gift codes with a fixed value will be credited directly to your wallet upon successful redemption.') }}</li>
                <li>{{ __('Each gift code can only be redeemed once per account.') }}</li>
                <li>{{ __('Please check the expiration date and usage limits of the gift code.') }}</li>
            </ul>
        </div>
    </div>

    {{-- Danh sách mã quà tặng/giảm giá công khai (Cột phải) --}}
    <div class="lg:col-span-7 flex flex-col gap-4 lg:pl-6 lg:border-l lg:border-app-border/60">
        <h3 class="text-xs sm:text-sm font-bold text-app-text flex items-center gap-1.5">
            <span class="material-symbols-outlined text-primary text-lg">redeem</span>
            {{ __('Active Gift Codes') }}
        </h3>
        
        @if ($publicCoupons->isEmpty())
            <div class="flex-1 min-h-[180px] border border-app-border rounded-xl bg-app-surface flex flex-col items-center justify-center text-center p-6 shadow-inner">
                <span class="material-symbols-outlined text-4xl text-app-muted/30 mb-2 font-light">confirmation_number</span>
                <p class="text-xs sm:text-sm text-app-muted font-medium">{{ __('There are currently no public gift codes available') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-h-[360px] overflow-y-auto pr-1 custom-scrollbar">
                @foreach ($publicCoupons as $coupon)
                    @php
                        $valueText = $coupon->type === 'percent' ? (number_format((float)$coupon->value, 0) . '%') : (number_format((float)$coupon->value, 0, ',', '.') . 'đ');
                        $minOrderText = $coupon->min_order > 0 ? (__('Min Order:') . ' ' . number_format((float)$coupon->min_order, 0, ',', '.') . 'đ') : __('Min Order 0đ');
                    @endphp
                    <div class="relative bg-app-surface border border-app-border rounded-xl p-4 flex flex-col justify-between gap-3 overflow-hidden shadow-sm hover:border-primary/40 transition-all group">
                        {{-- Hiệu ứng ticket voucher --}}
                        <div class="absolute -left-2 top-1/2 -translate-y-1/2 w-4 h-4 bg-app-main border-r border-app-border rounded-full pointer-events-none"></div>
                        <div class="absolute -right-2 top-1/2 -translate-y-1/2 w-4 h-4 bg-app-main border-l border-app-border rounded-full pointer-events-none"></div>
                        
                        <div class="flex items-start justify-between gap-2 pl-2 pr-2">
                            <div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-extrabold font-mono uppercase tracking-wider">
                                    {{ $coupon->code }}
                                </span>
                                <h4 class="text-xs font-bold text-app-text mt-2">{{ __('Value:') }} {{ $valueText }}</h4>
                                <p class="text-[9px] text-app-muted mt-1 font-medium">{{ $minOrderText }}</p>
                            </div>

                            {{-- Sao chép nhanh mã coupon --}}
                            <div x-data="{ copied: false }">
                                <button type="button" 
                                    @click="navigator.clipboard.writeText('{{ $coupon->code }}').then(() => { copied = true; couponInput = '{{ $coupon->code }}'; setTimeout(() => copied = false, 1500) })"
                                    class="size-8 rounded-lg border border-app-border bg-app-main hover:bg-app-hover flex items-center justify-center transition-all group/btn"
                                    :class="copied ? 'text-emerald-500 border-emerald-500/30 bg-emerald-500/5' : 'text-app-muted hover:text-app-text'"
                                    title="{{ __('Copy Code') }}">
                                    <span class="material-symbols-outlined text-[16px] transition-transform duration-200"
                                          :class="copied ? 'scale-110 font-bold' : 'group-hover/btn:scale-110'"
                                          x-text="copied ? 'check' : 'content_copy'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Tiến trình sử dụng --}}
                        @if ($coupon->max_uses !== null && $coupon->max_uses > 0)
                            @php
                                $percentUsed = min(100, round(($coupon->used_count / $coupon->max_uses) * 100));
                            @endphp
                            <div class="pl-2 pr-2 pt-1">
                                <div class="flex justify-between text-[8px] text-app-muted font-bold uppercase tracking-wider mb-1">
                                    <span>{{ __('Used') }}</span>
                                    <span>{{ $percentUsed }}%</span>
                                </div>
                                <div class="w-full bg-app-main rounded-full h-1">
                                    <div class="h-full rounded-full bg-primary/70 transition-all duration-500" style="width: {{ $percentUsed }}%"></div>
                                </div>
                            </div>
                        @else
                            <div class="pl-2 pr-2 pt-1 border-t border-app-border/40 text-[9px] text-app-muted font-medium italic flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">schedule</span> {{ __('No redemption limit') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
