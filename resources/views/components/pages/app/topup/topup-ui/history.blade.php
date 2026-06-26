{{-- VIEW: Lịch sử nạp tiền --}}
<div x-show="topupView === 'history'" x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak>

    {{-- Skeleton loading lần đầu — mô phỏng 4 transaction items --}}
    <div x-show="transactions.length === 0 && isLoadingHistory"
        class="bg-app-surface border border-app-border rounded-xl overflow-hidden" x-cloak>
        <div class="px-6 py-4 border-b border-app-border">
            <div class="h-4 skeleton-shimmer rounded w-1/3"></div>
        </div>
        <div class="p-6 space-y-3">
            <template x-for="i in 4" :key="'topup-hist-skel-' + i">
                <div class="flex items-center justify-between p-4 gap-3 rounded-xl border border-app-border">
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="size-10 rounded-full skeleton-shimmer shrink-0"></div>
                        <div class="flex flex-col gap-1.5">
                            <div class="h-3.5 skeleton-shimmer rounded w-24"></div>
                            <div class="h-2.5 skeleton-shimmer rounded w-32"></div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1.5">
                        <div class="h-5 skeleton-shimmer rounded-full w-16"></div>
                        <div class="h-2.5 skeleton-shimmer rounded w-20"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>

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
                                :class="{
                                    'bg-emerald-500/10 text-emerald-500': item.status === 'SUCCESS',
                                    'bg-amber-500/10 text-amber-500': item.status === 'PENDING',
                                    'bg-app-muted/10 text-app-muted': item.status === 'EXPIRED',
                                    'bg-red-500/10 text-red-500': item.status === 'CANCELLED' || item.status === 'FAILED',
                                }">
                                <span class="material-symbols-outlined text-[20px]"
                                    x-text="item.status === 'SUCCESS' ? 'check_circle' : (item.status === 'PENDING' ? 'schedule' : (item.status === 'EXPIRED' ? 'error' : 'cancel'))"></span>
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
                                :class="{
                                    'bg-emerald-500/10 text-emerald-500': item.status === 'SUCCESS',
                                    'bg-amber-500/10 text-amber-500': item.status === 'PENDING',
                                    'bg-app-muted/10 text-app-muted': item.status === 'EXPIRED',
                                    'bg-red-500/10 text-red-500': item.status === 'CANCELLED' || item.status === 'FAILED',
                                }"
                                x-text="item.status === 'SUCCESS' ? '{{ __('Success') }}' : (item.status === 'PENDING' ? '{{ __('Pending') }}' : (item.status === 'EXPIRED' ? '{{ __('Expired') }}' : (item.status === 'CANCELLED' ? '{{ __('Cancelled') }}' : '{{ __('Failed') }}')))">
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
