<x-app-layout :title="__('Gift - NDHGift')">
    @once
        <style>
            .scrollbar-none::-webkit-scrollbar {
                display: none;
            }

            .scrollbar-none {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    @endonce

    <script>
        function giftPage() {
            return {
                // Lấy chủ đề mặc định từ URL query parameter (ví dụ: ?category=love) hoặc mặc định là 'all'
                activeCategory: new URLSearchParams(window.location.search).get('category') || 'all',
                searchQuery: '',
                categories: [
                    { id: 'all', label: '{{ __('All') }}', icon: 'grid_view' },
                    @foreach($categories as $category)
                        { id: '{{ $category->slug }}', label: '{{ __($category->name) }}', icon: '{{ $category->icon }}' },
                    @endforeach
                    ],
                // Danh sách quà tặng được load động từ Database qua GiftService
                gifts: @json($gifts),
                init() {
                    // Theo dõi sự thay đổi của chủ đề được chọn để đồng bộ hóa lên URL
                    this.$watch('activeCategory', (value) => {
                        const url = new URL(window.location.href);
                        if (value === 'all') {
                            url.searchParams.delete('category');
                        } else {
                            url.searchParams.set('category', value);
                        }
                        window.history.replaceState({}, '', url.toString());
                    });
                },
                formatNumber(num) {
                    if (num >= 1000) {
                        let formatted = (num / 1000).toFixed(1);
                        return formatted.endsWith('.0') ? formatted.slice(0, -2) + 'k' : formatted + 'k';
                    }
                    return num;
                },
                get filteredGifts() {
                    return this.gifts.filter(gift => {
                        const matchesCategory = this.activeCategory === 'all' || gift.category === this.activeCategory;
                        const matchesSearch = gift.title.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchesCategory && matchesSearch;
                    });
                },
                selectedGift: null,
                couponCode: '',
                couponDiscount: 0,
                couponError: '',
                couponSuccessMessage: '',
                isApplyingCoupon: false,
                isSubmittingPayment: false,
                userBalance: {{ auth()->check() ? auth()->user()->balance : 0 }},
                isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},
                get totalPayment() {
                    if (!this.selectedGift) return 0;
                    return Math.max(0, this.selectedGift.price - this.couponDiscount);
                },
                openPaymentModal(gift) {
                    if (!this.isLoggedIn) {
                        // Chuyển hướng đến trang đăng nhập nếu chưa đăng nhập
                        window.location.href = "{{ route('login', ['locale' => app()->getLocale()]) }}";
                        return;
                    }
                    this.selectedGift = gift;
                    this.couponCode = '';
                    this.couponDiscount = 0;
                    this.couponError = '';
                    this.couponSuccessMessage = '';
                    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'confirm-payment' }));
                },
                closePaymentModal() {
                    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'confirm-payment' }));
                    // Tránh nhấp nháy giao diện khi ẩn modal
                    setTimeout(() => {
                        this.selectedGift = null;
                        this.couponCode = '';
                        this.couponDiscount = 0;
                        this.couponError = '';
                        this.couponSuccessMessage = '';
                    }, 300);
                },
                async applyCoupon() {
                    if (!this.couponCode.trim()) return;
                    this.isApplyingCoupon = true;
                    this.couponError = '';
                    this.couponSuccessMessage = '';

                    try {
                        const response = await fetch('{{ route('api.apply-coupon', ['locale' => app()->getLocale()]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                code: this.couponCode,
                                subtotal: this.selectedGift.price
                            })
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            this.couponDiscount = parseFloat(data.discount);
                            this.couponSuccessMessage = data.message || 'Áp dụng mã giảm giá thành công!';
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    type: 'success',
                                    title: 'Thành công',
                                    message: data.message
                                }
                            }));
                        } else {
                            this.couponDiscount = 0;
                            this.couponError = data.message || 'Mã giảm giá không hợp lệ hoặc đã hết hạn.';
                            window.dispatchEvent(new CustomEvent('toast', {
                                detail: {
                                    type: 'error',
                                    title: 'Thất bại',
                                    message: this.couponError
                                }
                            }));
                        }
                    } catch (error) {
                        console.error('Lỗi khi áp dụng coupon:', error);
                        this.couponError = 'Lỗi kết nối máy chủ.';
                    } finally {
                        this.isApplyingCoupon = false;
                    }
                },
                async submitPayment() {
                    if (this.userBalance < this.totalPayment) {
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                type: 'error',
                                title: 'Số dư không đủ',
                                message: 'Vui lòng nạp thêm tiền vào tài khoản để thực hiện giao dịch này.'
                            }
                        }));
                        return;
                    }

                    this.isSubmittingPayment = true;

                    try {
                        // Mô phỏng xử lý thanh toán 1.5s
                        await new Promise(resolve => setTimeout(resolve, 1500));

                        this.userBalance -= this.totalPayment;

                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: {
                                type: 'success',
                                title: 'Thành công',
                                message: 'Cảm ơn bạn đã mua món quà tặng này!'
                            }
                        }));

                        this.closePaymentModal();

                        // Tải lại trang sau khi thanh toán thành công
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);

                    } catch (error) {
                        console.error('Lỗi thanh toán:', error);
                    } finally {
                        this.isSubmittingPayment = false;
                    }
                }
            };
        }
    </script>

    {{-- Tiêu đề trang & Breadcrumbs --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-app-border/40 pb-5">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-app-text">{{ __('Gift') }}</h1>
            <p class="text-app-muted text-sm">{{ __('Create an online gift to send to your friends.') }}</p>
        </div>
        <!-- Breadcrumbs chỉ hiển thị trên desktop -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
            <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                NDHGift
            </a>
            <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
            <span class="text-app-text">{{ __('Gift') }}</span>
        </nav>
    </div>

    {{-- Khởi tạo Alpine.js cho giao diện danh sách quà tặng --}}
    <div x-data="giftPage()" class="space-y-6 mt-6">

        {{-- Thanh tìm kiếm & Tabs chủ đề --}}
        <div class="grid grid-cols-12 gap-4 items-center border-b border-app-border/40 pb-4">

            {{-- Tabs chủ đề (Mobile: chiếm 12 cột ở dưới, Desktop: chiếm 9 cột bên trái) --}}
            <div class="col-span-12 lg:col-span-9 order-2 lg:order-1 relative w-full flex items-center group/nav"
                x-data="{
                    showLeftArrow: false,
                    showRightArrow: false,
                    scrollTabs(direction) {
                        const container = this.$refs.tabContainer;
                        const scrollAmount = 180;
                        container.scrollBy({
                            left: direction === 'left' ? -scrollAmount : scrollAmount,
                            behavior: 'smooth'
                        });
                    },
                    checkScroll() {
                        const container = this.$refs.tabContainer;
                        if (!container) return;
                        this.showLeftArrow = container.scrollLeft > 5;
                        this.showRightArrow = container.scrollLeft < (container.scrollWidth - container.clientWidth - 5);
                    }
                }" x-init="
                    $nextTick(() => { 
                        checkScroll();
                        $refs.tabContainer.addEventListener('scroll', () => checkScroll());
                        window.addEventListener('resize', () => checkScroll());
                    });
                ">
                <!-- Mũi tên trái (với gradient fade) -->
                <div x-show="showLeftArrow" x-cloak
                    class="absolute left-0 top-0 bottom-1.5 w-12 bg-gradient-to-r from-app-main via-app-main/80 to-transparent z-10 flex items-center justify-start pointer-events-none transition-all duration-300">
                    <button @click="scrollTabs('left')"
                        class="pointer-events-auto text-app-muted hover:text-primary active:scale-90 transition-all duration-300 flex items-center justify-center group/btn size-8">
                        <span
                            class="material-symbols-outlined text-[20px] sm:text-[24px] select-none transition-transform duration-300 group-hover/btn:-translate-x-1">chevron_left</span>
                    </button>
                </div>

                <!-- Vùng chứa các tab (scroll) -->
                <div x-ref="tabContainer"
                    class="flex items-center gap-2 overflow-x-auto pb-1.5 scrollbar-none w-full scroll-smooth">
                    <template x-for="cat in categories" :key="cat.id">
                        <button @click="activeCategory = cat.id; $nextTick(() => checkScroll())"
                            :class="activeCategory === cat.id ? 'bg-primary text-white border-primary shadow-sm shadow-primary/10' : 'bg-app-surface text-app-muted border-app-border hover:bg-app-main/10 hover:text-app-text'"
                            class="px-4 py-2 rounded-xl border text-xs sm:text-sm font-semibold whitespace-nowrap transition-all duration-300 flex items-center gap-1.5 active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[16px] sm:text-[18px]" x-text="cat.icon"></span>
                            <span x-text="cat.label"></span>
                        </button>
                    </template>
                </div>

                <!-- Mũi tên phải (với gradient fade) -->
                <div x-show="showRightArrow" x-cloak
                    class="absolute right-0 top-0 bottom-1.5 w-12 bg-gradient-to-l from-app-main via-app-main/80 to-transparent z-10 flex items-center justify-end pointer-events-none transition-all duration-300">
                    <button @click="scrollTabs('right')"
                        class="pointer-events-auto text-app-muted hover:text-primary active:scale-90 transition-all duration-300 flex items-center justify-center group/btn size-8">
                        <span
                            class="material-symbols-outlined text-[20px] sm:text-[24px] select-none transition-transform duration-300 group-hover/btn:translate-x-1">chevron_right</span>
                    </button>
                </div>
            </div>

            {{-- Tìm kiếm mẫu (Mobile: chiếm 12 cột ở trên, Desktop: chiếm 3 cột bên phải) --}}
            <div class="col-span-12 lg:col-span-3 order-1 lg:order-2 w-full">
                <div class="relative w-full">
                    <span
                        class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-app-muted text-[20px] select-none pointer-events-none">search</span>
                    <input type="text" x-model="searchQuery" placeholder="{{ __('Tìm kiếm mẫu quà tặng...') }}"
                        class="w-full h-11 pl-11 pr-10 bg-app-surface border border-app-border rounded-xl text-sm text-app-text focus:border-primary focus:ring-1 focus:ring-primary/20 transition-all outline-none" />
                    <button x-show="searchQuery !== ''" @click="searchQuery = ''"
                        class="absolute right-3.5 top-1/2 -translate-y-1/2 text-app-muted hover:text-app-text flex items-center">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- Grid danh sách quà tặng (Mobile: 2 cột, Tablet: 3 cột, Desktop: 4 cột) --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
            <template x-for="gift in filteredGifts" :key="gift.id">
                <div
                    class="group bg-app-surface border border-app-border rounded-2xl overflow-hidden hover:shadow-md hover:border-primary/20 transition-all duration-300 flex flex-col relative">

                    {{-- Ảnh bìa mẫu --}}
                    <div class="relative w-full h-32 sm:h-44 bg-app-main/5 overflow-hidden group/img-container">
                        <!-- Ảnh chính -->
                        <img :src="gift.image" :alt="gift.title"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />

                        <!-- Lớp phủ overlay khi hover -->
                        <div
                            class="absolute inset-0 bg-black/40 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <!-- Nút Xem mẫu ở giữa ảnh -->
                            <a :href="gift.demo_url"
                                class="flex items-center gap-1 sm:gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-black/60 hover:bg-black/85 backdrop-blur-md border border-white/50 text-white rounded-full text-[10px] sm:text-xs font-bold transform scale-90 group-hover:scale-100 transition-all duration-300 shadow-md">
                                <span
                                    class="material-symbols-outlined text-[14px] sm:text-[16px] select-none">visibility</span>
                                <span class="whitespace-nowrap">{{ __('Xem chi tiết') }}</span>
                            </a>
                        </div>

                        <!-- Nhãn Hot Trend ở góc trên bên trái (nếu có is_hot) -->
                        <template x-if="gift.is_hot">
                            <span
                                class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-red-600 text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider select-none flex items-center gap-0.5 shadow-sm">
                                <span
                                    class="material-symbols-outlined text-[10px] sm:text-[12px] fill-current">local_fire_department</span>
                                <span>HOT</span>
                            </span>
                        </template>

                        <!-- Nhãn Star ở góc trên bên phải khi hover -->
                        <div
                            class="absolute top-2.5 right-2.5 px-1.5 py-0.5 rounded-lg bg-black/50 backdrop-blur-md text-[9px] sm:text-[10px] font-bold text-white flex items-center gap-0.5 select-none opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-sm">
                            <span
                                class="material-symbols-outlined text-[10px] sm:text-[12px] text-yellow-400 fill-current">star</span>
                            <span x-text="formatNumber(gift.stars)"></span>
                        </div>
                    </div>

                    {{-- Thông tin chi tiết --}}
                    <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between gap-2">
                        <div class="space-y-1">
                            <h3 class="text-xs sm:text-sm font-bold text-app-text line-clamp-2 leading-snug group-hover:text-primary transition-colors duration-200"
                                x-text="gift.title"></h3>

                            <!-- Dòng thống kê: Đã bán & Giá cũ -->
                            <div class="flex items-center justify-between mt-2 gap-1">
                                <!-- Đã bán (Lửa đỏ) -->
                                <div
                                    class="flex items-center gap-0.5 text-[10px] sm:text-xs font-semibold text-rose-500">
                                    <span
                                        class="material-symbols-outlined text-[13px] sm:text-[15px] text-rose-500 fill-current select-none">local_fire_department</span>
                                    <span>{{ __('Đã bán:') }} <span x-text="gift.sold"></span></span>
                                </div>
                                <!-- Giá cũ -->
                                <div class="text-[10px] sm:text-xs text-app-muted/80 line-through whitespace-nowrap"
                                    x-text="new Intl.NumberFormat('vi-VN').format(gift.old_price) + ' VND'"></div>
                            </div>

                            <!-- Dòng giá mới & nhãn giảm giá -->
                            <div class="flex items-center justify-end gap-1 mt-0.5">
                                <span class="text-xs sm:text-base font-bold text-rose-600 whitespace-nowrap"
                                    x-text="new Intl.NumberFormat('vi-VN').format(gift.price) + ' VND'"></span>
                                <span
                                    class="text-[9px] sm:text-[10px] font-bold text-primary bg-primary/10 border border-primary/20 px-1 py-0.5 rounded whitespace-nowrap"
                                    x-text="'-' + gift.discount + '%'"></span>
                            </div>
                        </div>

                        <!-- Nhóm nút hành động: Mua ngay & Xem demo -->
                        <div class="grid grid-cols-2 gap-1.5 sm:gap-2 mt-2">
                            <!-- Nút Mua ngay — chuyển đến trang chỉnh sửa nội dung -->
                            <a :href="gift.create_url"
                                class="flex items-center justify-center gap-0.5 sm:gap-1.5 py-1 sm:py-2 px-1 sm:px-3 bg-primary hover:bg-primary/95 text-white rounded-xl text-[9px] sm:text-xs font-bold transition-all shadow-sm shadow-primary/10 active:scale-[0.97] whitespace-nowrap">
                                <span
                                    class="material-symbols-outlined text-[12px] sm:text-[16px] select-none">edit</span>
                                <span>{{ __('Tạo quà tặng') }}</span>
                            </a>
                            <!-- Nút Xem demo -->
                            <a :href="gift.demo_url"
                                class="flex items-center justify-center gap-0.5 sm:gap-1.5 py-1 sm:py-2 px-1 sm:px-3 bg-app-surface hover:bg-app-main/5 border border-app-border text-app-text rounded-xl text-[9px] sm:text-xs font-bold transition-all active:scale-[0.97] whitespace-nowrap">
                                <span
                                    class="material-symbols-outlined text-[12px] sm:text-[16px] select-none">open_in_new</span>
                                <span>{{ __('Xem demo') }}</span>
                            </a>
                        </div>

                        <!-- Liên kết hướng dẫn & video hướng dẫn -->
                        <div
                            class="flex items-center justify-center gap-2 sm:gap-3 text-[9px] sm:text-[11px] mt-2 pt-2 border-t border-app-border/40">
                            <a :href="gift.guide_url"
                                class="flex items-center gap-0.5 sm:gap-1 text-primary font-semibold whitespace-nowrap">
                                <span
                                    class="material-symbols-outlined text-[11px] sm:text-[14px] select-none">help</span>
                                <span>{{ __('Hướng dẫn') }}</span>
                            </a>
                            <span class="text-app-border select-none">|</span>
                            <a :href="gift.video_url"
                                class="flex items-center gap-0.5 sm:gap-1 text-rose-500 font-semibold whitespace-nowrap">
                                <span
                                    class="material-symbols-outlined text-[11px] sm:text-[14px] select-none">play_circle</span>
                                <span>{{ __('Video hướng dẫn') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Thông báo trống khi không tìm thấy kết quả --}}
        <div x-show="filteredGifts.length === 0" class="flex flex-col items-center justify-center py-12 text-center"
            x-cloak>
            <div class="size-16 rounded-full bg-app-main/30 flex items-center justify-center text-app-muted mb-3">
                <span class="material-symbols-outlined text-[32px]">sentiment_dissatisfied</span>
            </div>
            <p class="text-sm font-semibold text-app-text">{{ __('Không tìm thấy mẫu quà tặng') }}</p>
            <p class="text-xs text-app-muted mt-1">{{ __('Thử thay đổi từ khóa tìm kiếm hoặc chọn chủ đề khác.') }}</p>
        </div>

        <!-- Modal Xác nhận thanh toán -->
        <x-shared.ui.modal name="confirm-payment" maxWidth="md">
            <div class="p-6 flex flex-col gap-4 text-app-text">
                <!-- Tiêu đề và biểu tượng -->
                <div class="flex flex-col items-center text-center gap-2">
                    <span class="material-symbols-outlined text-primary text-4xl fill-primary/10">verified</span>
                    <h2 class="text-xl font-bold text-app-text">{{ __('Xác nhận thanh toán') }}</h2>
                    <p class="text-app-muted text-xs">{{ __('Vui lòng kiểm tra thông tin bên dưới') }}</p>
                </div>

                <!-- Bảng thông tin dịch vụ -->
                <div class="bg-app-main/10 border border-app-border/60 rounded-2xl p-4 flex flex-col gap-3">
                    <div class="flex items-center justify-between gap-4 py-1 border-b border-app-border/40">
                        <span class="text-xs text-app-muted font-medium">{{ __('Gói dịch vụ') }}</span>
                        <span class="text-xs font-bold text-app-text text-right line-clamp-1"
                            x-text="selectedGift?.title"></span>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-1 border-b border-app-border/40">
                        <span class="text-xs text-app-muted font-medium">{{ __('Thời hạn') }}</span>
                        <span class="text-xs font-bold text-app-text">{{ __('Vĩnh viễn') }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4 py-1">
                        <span class="text-xs text-app-muted font-medium">{{ __('Trang quà tặng') }}</span>
                        <span
                            class="text-xs font-bold text-app-text text-right line-clamp-1 max-w-[200px]">{{ __('Bạn nhận được món quà từ...') }}</span>
                    </div>
                </div>

                <!-- Ô nhập mã giảm giá -->
                <div class="flex flex-col gap-2">
                    <label class="text-xs font-bold text-primary flex items-center gap-1">
                        <span class="material-symbols-outlined text-base">local_offer</span>
                        {{ __('Mã giảm giá') }}
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="couponCode" placeholder="NHẬP MÃ GIẢM GIÁ (NẾU CÓ)..."
                            :disabled="isApplyingCoupon || couponDiscount > 0"
                            class="flex-1 h-10 px-3 bg-app-main border border-app-border rounded-xl text-xs text-app-text font-mono uppercase tracking-wider focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all disabled:opacity-60" />
                        <button type="button" @click="applyCoupon()"
                            :disabled="isApplyingCoupon || !couponCode.trim() || couponDiscount > 0"
                            class="h-10 px-4 bg-primary hover:bg-primary/90 disabled:opacity-50 text-white text-xs font-bold rounded-xl flex items-center gap-1 transition-all active:scale-[0.98]">
                            <span x-show="isApplyingCoupon"
                                class="animate-spin border-2 border-white/30 border-t-white rounded-full size-3.5 inline-block"></span>
                            <span class="material-symbols-outlined text-base" x-show="!isApplyingCoupon">check</span>
                            <span>{{ __('Áp dụng') }}</span>
                        </button>
                    </div>
                    <!-- Thông báo lỗi hoặc thành công của Coupon -->
                    <div class="text-[10px] font-semibold mt-0.5" :class="couponError ? 'text-red-500' : 'text-primary'"
                        x-show="couponError || couponSuccessMessage">
                        <span x-text="couponError || couponSuccessMessage"></span>
                    </div>
                </div>

                <!-- Tổng thanh toán -->
                <div class="flex items-center justify-between border-t border-app-border/40 pt-4 mt-2">
                    <span class="text-sm font-bold text-app-text">{{ __('Tổng thanh toán') }}</span>
                    <span class="text-lg font-extrabold text-primary"
                        x-text="new Intl.NumberFormat('vi-VN').format(totalPayment) + 'đ'"></span>
                </div>

                <!-- Chú thích trừ tiền -->
                {{-- <p class="text-[10px] text-app-muted text-center flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-xs select-none">info</span>
                    {{ __('Số tiền sẽ được trừ trực tiếp từ số dư tài khoản của bạn.') }}
                </p> --}}

                <!-- Hủy và Xác nhận thanh toán -->
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <button type="button" @click="closePaymentModal()"
                        class="py-2.5 px-4 bg-gray-500/20 hover:bg-app-main/30 text-app-text text-xs font-bold rounded-xl transition-all active:scale-[0.98]">
                        {{ __('Hủy') }}
                    </button>
                    <button type="button" @click="submitPayment()" :disabled="isSubmittingPayment"
                        class="py-2.5 px-4 bg-primary hover:bg-primary/90 disabled:opacity-50 text-white text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 transition-all shadow-md active:scale-[0.98]">
                        <span x-show="isSubmittingPayment"
                            class="animate-spin border-2 border-white/30 border-t-white rounded-full size-3.5 inline-block"></span>
                        <span class="material-symbols-outlined text-base">credit_card</span>
                        <span>{{ __('Xác nhận thanh toán') }}</span>
                    </button>
                </div>

                <!-- Số dư tài khoản dưới cùng -->
                <div
                    class="flex items-center justify-center gap-1 mt-2 text-xs font-medium text-app-muted border-t border-app-border/40 pt-3">
                    <span class="material-symbols-outlined text-[16px] text-app-muted">account_balance_wallet</span>
                    <span>{{ __('Số dư khả dụng:') }}</span>
                    <span class="font-bold text-app-text"
                        x-text="new Intl.NumberFormat('vi-VN').format(userBalance) + 'đ'"></span>
                </div>
            </div>
        </x-shared.ui.modal>
    </div>

</x-app-layout>