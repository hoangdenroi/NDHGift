<div class="space-y-4 sm:space-y-6" x-data="{ 
    filter: 'month',
    chartData: @js($chartData),
    get active() { return this.chartData[this.filter]; },
    get maxVal() { 
        let max = Math.max(...this.active.values);
        return max > 0 ? max : 1; 
    },
    formatYLabel(val) {
        if (val <= 1) return '0';
        if (val >= 1000000) {
            return (val / 1000000).toFixed(val % 1000000 === 0 ? 0 : 1) + 'M';
        }
        if (val >= 1000) {
            return (val / 1000).toFixed(val % 1000 === 0 ? 0 : 1) + 'k';
        }
        return Math.round(val);
    }
}">
    {{-- Box Lịch sử nạp tiền (Premium Column Chart) --}}
    <div class="border border-app-border rounded-xl bg-app-surface overflow-hidden shadow-sm">
        <div class="p-4 border-b border-app-border flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-xl">bar_chart</span>
                <h3 class="text-sm font-bold text-app-text">Thống kê nạp tiền</h3>
            </div>
            
            {{-- Bộ lọc Ngày/Tháng/Năm --}}
            <div class="flex items-center bg-app-main border border-app-border rounded-lg p-0.5 shadow-inner">
                <button type="button" @click="filter = 'day'"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition-all duration-200"
                    :class="filter === 'day' ? 'bg-app-surface text-primary shadow-sm border border-app-border' : 'text-app-muted hover:text-app-text'">
                    Ngày
                </button>
                <button type="button" @click="filter = 'month'"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition-all duration-200"
                    :class="filter === 'month' ? 'bg-app-surface text-primary shadow-sm border border-app-border' : 'text-app-muted hover:text-app-text'">
                    Tháng
                </button>
                <button type="button" @click="filter = 'year'"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition-all duration-200"
                    :class="filter === 'year' ? 'bg-app-surface text-primary shadow-sm border border-app-border' : 'text-app-muted hover:text-app-text'">
                    Năm
                </button>
            </div>
        </div>
        
        <div class="p-4 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Biểu đồ cột (Cột trái) --}}
            <div class="lg:col-span-8 flex flex-col justify-end min-h-[220px]">
                {{-- Flex container chứa Trục Y và Vùng vẽ cột --}}
                <div class="flex items-stretch h-48">
                    
                    {{-- Trục Y (Hiển thị các mốc tiền ở bên trái) --}}
                    <div class="w-9 sm:w-12 flex flex-col justify-between text-[8px] sm:text-[10px] text-app-muted font-bold text-right pr-1.5 sm:pr-3 select-none pt-6 pb-2 shrink-0">
                        <span x-text="formatYLabel(maxVal)"></span>
                        <span x-text="formatYLabel(maxVal * 0.75)"></span>
                        <span x-text="formatYLabel(maxVal * 0.5)"></span>
                        <span x-text="formatYLabel(maxVal * 0.25)"></span>
                        <span>0</span>
                    </div>
                    
                    {{-- Vùng vẽ cột --}}
                    <div class="flex-1 relative flex items-end gap-1 sm:gap-4 pt-6 border-b border-app-border/40 pb-2 pr-2 sm:pr-0">
                        {{-- Gridlines ngang phía sau --}}
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none select-none pt-6 pb-2">
                            <div class="w-full border-t border-app-border/5"></div>
                            <div class="w-full border-t border-app-border/5"></div>
                            <div class="w-full border-t border-app-border/5"></div>
                            <div class="w-full border-t border-app-border/5"></div>
                        </div>
                        
                        {{-- Các cột biểu đồ render động --}}
                        <template x-for="(val, index) in active.values" :key="index">
                            <div class="flex-1 flex flex-col items-center group relative h-full justify-end">
                                {{-- Thân cột có màu primary rực rỡ và hiệu ứng tương tác --}}
                                <div class="w-full bg-primary opacity-90 hover:opacity-100 rounded-t-md transition-all duration-500 ease-out cursor-pointer relative shadow-sm shadow-primary/5"
                                     :style="{ height: ((val / maxVal) * 100) + '%' }"
                                     style="min-height: 6px;">
                                     
                                     {{-- Bong bóng số tiền luôn hiển thị trên đầu thân cột (chỉ hiện khi val > 0) --}}
                                     <div x-show="val > 0" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 flex flex-col items-center z-10">
                                         {{-- Nhãn rút gọn trên Mobile --}}
                                         <span class="bg-app-surface border border-app-border text-app-text font-bold px-1 py-0.5 rounded shadow-sm whitespace-nowrap block sm:hidden text-[7px]"
                                               x-text="formatYLabel(val)">
                                         </span>
                                         {{-- Nhãn đầy đủ trên Desktop/Tablet --}}
                                         <span class="bg-app-surface border border-app-border text-app-text font-bold px-1.5 py-0.5 rounded-lg shadow-sm whitespace-nowrap hidden sm:block text-[10px]"
                                               x-text="new Intl.NumberFormat('vi-VN').format(val) + 'đ'">
                                         </span>
                                         <div class="w-1 h-1 sm:w-1.5 sm:h-1.5 bg-app-surface border-r border-b border-app-border rotate-45 -mt-0.5 sm:-mt-1"></div>
                                     </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                {{-- Trục X --}}
                <div class="flex justify-between gap-1 sm:gap-4 pt-2 text-[8px] sm:text-xs font-semibold text-app-muted pl-9 sm:pl-12 pr-2 sm:pr-0">
                    <template x-for="(label, index) in active.labels" :key="index">
                        <span class="flex-1 text-center text-[8px] sm:text-[10px] font-bold" x-text="label"></span>
                    </template>
                </div>
            </div>
            
            {{-- Số liệu tài chính thống kê nhanh (Cột phải) --}}
            <div class="lg:col-span-4 flex flex-col gap-4 justify-between border-t lg:border-t-0 lg:border-l border-app-border/50 pt-4 lg:pt-0 lg:pl-6">
                <div class="p-4 bg-app-main border border-app-border rounded-xl flex items-center gap-3 shadow-inner">
                    <div class="size-9 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-500 shrink-0">
                        <span class="material-symbols-outlined text-lg">account_balance_wallet</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-app-muted uppercase font-bold tracking-wider mb-0.5">Tổng tiền đã nạp</p>
                        <h4 class="text-sm sm:text-base font-extrabold text-app-text truncate" x-text="new Intl.NumberFormat('vi-VN').format(active.total) + 'đ'"></h4>
                    </div>
                </div>

                <div class="p-4 bg-app-main border border-app-border rounded-xl flex items-center gap-3 shadow-inner">
                    <div class="size-9 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0">
                        <span class="material-symbols-outlined text-lg">done_all</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-app-muted uppercase font-bold tracking-wider mb-0.5">Số lần nạp thành công</p>
                        <h4 class="text-sm sm:text-base font-extrabold text-app-text truncate" x-text="active.count + ' lần'"></h4>
                    </div>
                </div>

                <div class="p-4 bg-app-main border border-app-border rounded-xl flex items-center gap-3 shadow-inner">
                    <div class="size-9 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 shrink-0">
                        <span class="material-symbols-outlined text-lg">analytics</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-app-muted uppercase font-bold tracking-wider mb-0.5">Mức nạp trung bình</p>
                        <h4 class="text-sm sm:text-base font-extrabold text-app-text truncate" x-text="new Intl.NumberFormat('vi-VN').format(active.avg) + 'đ'"></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hướng dẫn nhanh sử dụng ví nạp tiền và quà tặng --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
        <div class="border border-app-border rounded-xl bg-app-surface p-5 shadow-sm">
            <h4 class="font-bold text-app-text text-sm mb-3 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary">payments</span>
                Hướng dẫn nạp tiền tự động
            </h4>
            <ul class="text-xs text-app-muted space-y-2 list-decimal list-inside font-medium">
                <li>Truy cập tab <strong class="text-app-text">Nạp tiền</strong> để lấy mã QR thanh toán ngân hàng.</li>
                <li>Hệ thống tạo mã QR chứa thông tin chuyển khoản định danh riêng cho bạn.</li>
                <li>Chuyển khoản chính xác số tiền và nội dung. Tiền được cộng tự động sau 30s - 1 phút.</li>
                <li>Không thay đổi nội dung chuyển khoản để hệ thống tự nhận diện chính xác.</li>
            </ul>
        </div>

        <div class="border border-app-border rounded-xl bg-app-surface p-5 shadow-sm">
            <h4 class="font-bold text-app-text text-sm mb-3 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary">redeem</span>
                Quy đổi mã quà tặng
            </h4>
            <ul class="text-xs text-app-muted space-y-2 list-decimal list-inside font-medium">
                <li>Nhận các mã quà tặng (Coupon fixed) từ sự kiện hoặc admin phát tặng.</li>
                <li>Truy cập tab <strong class="text-app-text">Quy đổi mã quà tặng</strong> để nhập mã.</li>
                <li>Hệ thống xác nhận và cộng ngay mệnh giá của mã vào số dư ví của bạn.</li>
                <li>Mỗi tài khoản chỉ được áp dụng mỗi mã quà tặng một lần duy nhất.</li>
            </ul>
        </div>
    </div>
</div>
