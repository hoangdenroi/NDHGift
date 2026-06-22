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
                <h3 class="text-sm font-bold text-app-text">{{ __('Top Up Statistics') }}</h3>
            </div>
            
            {{-- Bộ lọc Ngày/Tháng/Năm --}}
            <div class="flex items-center bg-app-main border border-app-border rounded-lg p-0.5 shadow-inner">
                <button type="button" @click="filter = 'day'"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition-all duration-200"
                    :class="filter === 'day' ? 'bg-app-surface text-primary shadow-sm border border-app-border' : 'text-app-muted hover:text-app-text'">
                    {{ __('Day') }}
                </button>
                <button type="button" @click="filter = 'month'"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition-all duration-200"
                    :class="filter === 'month' ? 'bg-app-surface text-primary shadow-sm border border-app-border' : 'text-app-muted hover:text-app-text'">
                    {{ __('Month') }}
                </button>
                <button type="button" @click="filter = 'year'"
                    class="px-3 py-1 text-xs font-semibold rounded-md transition-all duration-200"
                    :class="filter === 'year' ? 'bg-app-surface text-primary shadow-sm border border-app-border' : 'text-app-muted hover:text-app-text'">
                    {{ __('Year') }}
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
                        <p class="text-[10px] text-app-muted uppercase font-bold tracking-wider mb-0.5">{{ __('Total Topped Up') }}</p>
                        <h4 class="text-sm sm:text-base font-extrabold text-app-text truncate" x-text="new Intl.NumberFormat('vi-VN').format(active.total) + 'đ'"></h4>
                    </div>
                </div>

                <div class="p-4 bg-app-main border border-app-border rounded-xl flex items-center gap-3 shadow-inner">
                    <div class="size-9 rounded-lg bg-blue-500/10 flex items-center justify-center text-blue-500 shrink-0">
                        <span class="material-symbols-outlined text-lg">done_all</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-app-muted uppercase font-bold tracking-wider mb-0.5">{{ __('Successful Top Ups') }}</p>
                        <h4 class="text-sm sm:text-base font-extrabold text-app-text truncate" x-text="active.count + ' {{ __('times') }}'"></h4>
                    </div>
                </div>

                <div class="p-4 bg-app-main border border-app-border rounded-xl flex items-center gap-3 shadow-inner">
                    <div class="size-9 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-500 shrink-0">
                        <span class="material-symbols-outlined text-lg">analytics</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[10px] text-app-muted uppercase font-bold tracking-wider mb-0.5">{{ __('Average Top Up') }}</p>
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
                {{ __('Automatic Top Up Guide') }}
            </h4>
            <ul class="text-xs text-app-muted space-y-2 list-decimal list-inside font-medium">
                <li>{{ __('Access tab') }} <strong class="text-app-text">{{ __('Top Up') }}</strong> {{ __('to get the bank payment QR code.') }}</li>
                <li>{{ __('Hệ thống tạo mã QR chứa thông tin chuyển khoản định danh riêng cho bạn.') }}</li>
                <li>{{ __('Transfer the exact amount and content. Balance is automatically updated in 30s - 1 minute.') }}</li>
                <li>{{ __('Do not change the transfer content for the system to identify correctly.') }}</li>
            </ul>
        </div>

        <div class="border border-app-border rounded-xl bg-app-surface p-5 shadow-sm">
            <h4 class="font-bold text-app-text text-sm mb-3 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-primary">redeem</span>
                {{ __('Redeem Gift Code') }}
            </h4>
            <ul class="text-xs text-app-muted space-y-2 list-decimal list-inside font-medium">
                <li>{{ __('Receive gift codes (fixed coupons) from events or system administrators.') }}</li>
                <li>{{ __('Access tab') }} <strong class="text-app-text">{{ __('Redeem Gift Code') }}</strong> {{ __('to enter the code.') }}</li>
                <li>{{ __('The system verifies and immediately adds the code value to your wallet balance.') }}</li>
                <li>{{ __('Each account can only apply each gift code once.') }}</li>
            </ul>
        </div>
    </div>
</div>
