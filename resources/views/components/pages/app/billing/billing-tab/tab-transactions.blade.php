<div class="border border-app-border rounded-xl bg-app-surface overflow-hidden shadow-sm">
    <div class="p-4 border-b border-app-border">
        <h3 class="font-bold text-app-text text-sm">Lịch sử giao dịch</h3>
        <p class="text-xs text-app-muted mt-0.5">Danh sách các hoạt động nạp tiền, quy đổi quà tặng và chi tiêu ví.</p>
    </div>

    @if($transactions->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 sm:py-24 text-center">
            <span class="material-symbols-outlined text-[36px] sm:text-[44px] text-app-muted/30 mb-3 font-light">swap_horiz</span>
            <p class="text-xs sm:text-sm text-app-muted font-medium">Bạn chưa thực hiện giao dịch nào</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-app-border text-app-muted font-bold bg-app-main/30">
                        <th class="py-3 px-4">Mã giao dịch</th>
                        <th class="py-3 px-4">Phương thức</th>
                        <th class="py-3 px-4">Nội dung</th>
                        <th class="py-3 px-4 text-right">Số tiền</th>
                        <th class="py-3 px-4 text-center">Trạng thái</th>
                        <th class="py-3 px-4 text-right">Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $tx)
                        <tr class="border-b border-app-border/40 hover:bg-app-hover/10 transition-colors">
                            <td class="py-4 px-4 font-mono font-bold text-app-text">
                                {{ $tx->transaction_no }}
                            </td>
                            <td class="py-4 px-4 font-bold">
                                @if($tx->payment_method === 'SEPAY')
                                    <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-500 text-[10px] uppercase font-mono tracking-wider">Chuyển khoản (SePay)</span>
                                @elseif($tx->payment_method === 'COUPON')
                                    <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-500 text-[10px] uppercase font-mono tracking-wider">Mã quà tặng</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-gray-500/10 text-gray-500 text-[10px] uppercase font-mono tracking-wider">{{ $tx->payment_method }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-app-text max-w-[200px] truncate" title="{{ $tx->order_info }}">
                                {{ $tx->order_info ?: 'Nạp tiền tài khoản' }}
                            </td>
                            <td class="py-4 px-4 text-right font-extrabold" :class="'{{ $tx->amount }}' > 0 ? 'text-emerald-500' : 'text-rose-500'">
                                +{{ number_format($tx->amount, 0, ',', '.') }}đ
                            </td>
                            <td class="py-4 px-4 text-center">
                                @if($tx->status === 'SUCCESS')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">Thành công</span>
                                @elseif($tx->status === 'PENDING')
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">Chờ xử lý</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/10 text-rose-500 border border-rose-500/20">Thất bại</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-right text-app-muted font-medium whitespace-nowrap">
                                {{ $tx->created_at->format('H:i d/m/Y') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{-- Phân trang --}}
        @if($transactions->hasPages())
            <div class="p-4 border-t border-app-border bg-app-main/10">
                {{ $transactions->links() }}
            </div>
        @endif
    @endif
</div>
