{{-- BẢNG GIAO DỊCH ĐANG CHỜ --}}
<div x-show="pendingTransactions.length > 0"
    class="mt-8 bg-app-surface border border-app-border rounded-xl overflow-hidden" x-transition x-cloak>
    <div class="px-6 py-4 border-b border-app-border flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="size-2 rounded-full bg-primary animate-pulse"></span>
            <h2 class="text-sm sm:text-base font-bold text-app-text">Giao dịch nạp tiền đang chờ thanh toán</h2>
        </div>
        <span class="text-xs font-semibold text-app-muted"
            x-text="`${pendingTransactions.filter(t => t.status === 'PENDING').length}/3 giao dịch`"></span>
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
                            <p class="text-sm font-bold text-primary"
                                x-text="new Intl.NumberFormat('vi-VN').format(tx.amount) + 'đ'"></p>
                        </div>
                    </div>

                    <!-- Nội dung CK -->
                    <div x-data="{ copied: false }"
                        class="bg-app-main p-2.5 rounded-lg border border-app-border flex items-center justify-between mb-4">
                        <div class="min-w-0 flex-1 pr-2">
                            <span class="text-[9px] text-app-muted block uppercase font-bold tracking-wider">Nội dung
                                chuyển khoản</span>
                            <span class="text-xs font-mono font-bold text-app-text select-all truncate block"
                                x-text="tx.order_info"></span>
                        </div>
                        <button @click="copyText(tx.order_info); copied = true; setTimeout(() => copied = false, 2000)"
                            :class="copied ? 'text-emerald-500 bg-emerald-500/10 hover:bg-emerald-500/20' : 'text-primary bg-primary/10 hover:bg-primary/20'"
                            class="p-1.5 rounded transition-colors shrink-0" title="Sao chép">
                            <span class="material-symbols-outlined text-[16px]"
                                x-text="copied ? 'check' : 'content_copy'"></span>
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
                                    <button @click="showQr(tx)"
                                        class="h-[26px] px-2.5 rounded bg-primary text-white text-[11px] font-bold hover:bg-primary/90 transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">qr_code</span> Xem QR
                                    </button>
                                    <button @click="confirmCancel(tx)"
                                        class="size-[26px] flex items-center justify-center rounded border border-red-500/20 hover:bg-red-500/10 text-red-500 transition-colors"
                                        title="Hủy giao dịch">
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

{{-- Modal xác nhận hủy giao dịch --}}
<x-shared.ui.modal name="confirm-cancel-topup" maxWidth="md">
    <div class="p-6">
        <div class="flex items-center gap-3 mb-4 text-red-500">
            <span class="material-symbols-outlined text-[28px]">warning</span>
            <h3 class="text-base sm:text-lg font-bold text-app-text">Xác nhận hủy giao dịch</h3>
        </div>
        <p class="text-xs sm:text-sm text-app-muted mb-6 leading-relaxed">
            Bạn có chắc chắn muốn hủy giao dịch nạp tiền mã giao dịch <span
                class="font-mono font-bold text-app-text uppercase" x-text="txToCancel?.payment_code"></span> với số
            tiền <span class="font-bold text-primary"
                x-text="txToCancel ? new Intl.NumberFormat('vi-VN').format(txToCancel.amount) + 'đ' : ''"></span> không?
            Hành động này không thể hoàn tác.
        </p>
        <div class="flex justify-end gap-3">
            <button @click="$dispatch('close-modal', 'confirm-cancel-topup')"
                class="px-4 py-2 rounded-xl border border-app-border hover:bg-app-main text-app-text text-xs sm:text-sm font-semibold transition-colors">
                Hủy bỏ
            </button>
            <button @click="executeCancelTx()"
                class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-xs sm:text-sm font-semibold transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                Đồng ý hủy
            </button>
        </div>
    </div>
</x-shared.ui.modal>