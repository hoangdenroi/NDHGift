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
    <div x-data="{
        activeCategory: 'all',
        searchQuery: '',
        categories: [
            { id: 'all', label: '{{ __('All') }}', icon: 'grid_view' },
            { id: 'birthday', label: '{{ __('Birthday') }}', icon: 'cake' },
            { id: 'love', label: '{{ __('Love') }}', icon: 'favorite' },
            { id: 'thank', label: '{{ __('Thank You') }}', icon: 'volunteer_activism' },
            { id: 'anniversary', label: '{{ __('Anniversary') }}', icon: 'celebration' },
            { id: 'christmas', label: '{{ __('Christmas') }}', icon: 'ac_unit' },
            { id: 'tet', label: '{{ __('Tết') }}', icon: 'ac_unit' },
            { id: 'mid_autumn', label: '{{ __('Tết Trung Thu') }}', icon: 'ac_unit' },
            { id: 'valentine', label: '{{ __('Lễ Tình Nhân') }}', icon: 'ac_unit' }
        ],
        gifts: [
            { 
                id: 1, 
                title: '{{ __('Birthday special - Bánh sinh nhật 3D thổi nến cắt bánh') }}', 
                category: 'birthday', 
                sold: 12, 
                stars: 1200,
                is_hot: true,
                image: '{{ asset('assets/images/gifts/birthday_cake_3d.png') }}', 
                old_price: 69998.6, 
                price: 49999, 
                discount: 40,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 2, 
                title: '{{ __('Web Trái Tim 3D – Gửi Yêu Thương Bay Lên 💖') }}', 
                category: 'love', 
                sold: 389, 
                stars: 35000,
                is_hot: true,
                image: '{{ asset('assets/images/gifts/heart_3d.png') }}', 
                old_price: 55998.6, 
                price: 39999, 
                discount: 40,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 3, 
                title: '{{ __('Thiệp Sinh Nhật 3D Lung Linh') }}', 
                category: 'birthday', 
                sold: 154, 
                stars: 950,
                is_hot: false,
                image: '{{ asset('assets/images/gifts/birthday_cake_3d.png') }}', 
                old_price: 79998.6, 
                price: 49999, 
                discount: 37,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 4, 
                title: '{{ __('Thư Tình Yêu 3D Lãng Mạn') }}', 
                category: 'love', 
                sold: 840, 
                stars: 105000,
                is_hot: true,
                image: '{{ asset('assets/images/gifts/heart_3d.png') }}', 
                old_price: 65998.6, 
                price: 39999, 
                discount: 39,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 5, 
                title: '{{ __('Lời Cảm Ơn 3D Sâu Sắc') }}', 
                category: 'thank', 
                sold: 92, 
                stars: 84,
                is_hot: false,
                image: '{{ asset('assets/images/gifts/birthday_cake_3d.png') }}', 
                old_price: 49998.6, 
                price: 29999, 
                discount: 40,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 6, 
                title: '{{ __('Kỷ Niệm Ngày Chung Đôi 3D') }}', 
                category: 'anniversary', 
                sold: 215, 
                stars: 1500,
                is_hot: true,
                image: '{{ asset('assets/images/gifts/heart_3d.png') }}', 
                old_price: 79998.6, 
                price: 49999, 
                discount: 37,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 7, 
                title: '{{ __('Giáng Sinh Ấm Áp 3D') }}', 
                category: 'christmas', 
                sold: 312, 
                stars: 3100,
                is_hot: false,
                image: '{{ asset('assets/images/gifts/heart_3d.png') }}', 
                old_price: 89998.6, 
                price: 59999, 
                discount: 33,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            },
            { 
                id: 8, 
                title: '{{ __('Hộp Quà Bí Mật 3D') }}', 
                category: 'birthday', 
                sold: 450, 
                stars: 12000,
                is_hot: true,
                image: '{{ asset('assets/images/gifts/birthday_cake_3d.png') }}', 
                old_price: 59998.6, 
                price: 39999, 
                discount: 33,
                demo_url: '#',
                guide_url: '#',
                video_url: '#'
            }
        ],
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
        }
    }" class="space-y-6 mt-6">

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
                        <div class="absolute inset-0 bg-black/40 backdrop-blur-[1px] opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                            <!-- Nút Xem mẫu ở giữa ảnh -->
                            <a :href="gift.demo_url" class="flex items-center gap-1 sm:gap-1.5 px-3 py-1.5 sm:px-4 sm:py-2 bg-black/60 hover:bg-black/85 backdrop-blur-md border border-white/50 text-white rounded-full text-[10px] sm:text-xs font-bold transform scale-90 group-hover:scale-100 transition-all duration-300 shadow-md">
                                <span class="material-symbols-outlined text-[14px] sm:text-[16px] select-none">visibility</span>
                                <span class="whitespace-nowrap">{{ __('Xem mẫu') }}</span>
                            </a>
                        </div>

                        <!-- Nhãn Hot Trend ở góc trên bên trái (nếu có is_hot) -->
                        <template x-if="gift.is_hot">
                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-red-600 text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider select-none flex items-center gap-0.5 shadow-sm">
                                <span class="material-symbols-outlined text-[10px] sm:text-[12px] fill-current">local_fire_department</span>
                                <span>HOT</span>
                            </span>
                        </template>

                        <!-- Nhãn Star ở góc trên bên phải khi hover -->
                        <div class="absolute top-2.5 right-2.5 px-1.5 py-0.5 rounded-lg bg-black/50 backdrop-blur-md text-[9px] sm:text-[10px] font-bold text-white flex items-center gap-0.5 select-none opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-sm">
                            <span class="material-symbols-outlined text-[10px] sm:text-[12px] text-yellow-400 fill-current">star</span>
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
                                <div class="flex items-center gap-0.5 text-[10px] sm:text-xs font-semibold text-rose-500">
                                    <span class="material-symbols-outlined text-[13px] sm:text-[15px] text-rose-500 fill-current select-none">local_fire_department</span>
                                    <span>{{ __('Đã bán:') }} <span x-text="gift.sold"></span></span>
                                </div>
                                <!-- Giá cũ -->
                                <div class="text-[10px] sm:text-xs text-app-muted/80 line-through whitespace-nowrap" x-text="new Intl.NumberFormat('vi-VN').format(gift.old_price) + ' VND'"></div>
                            </div>

                            <!-- Dòng giá mới & nhãn giảm giá -->
                            <div class="flex items-center justify-end gap-1 mt-0.5">
                                <span class="text-xs sm:text-base font-bold text-rose-600 whitespace-nowrap" x-text="new Intl.NumberFormat('vi-VN').format(gift.price) + ' VND'"></span>
                                <span class="text-[9px] sm:text-[10px] font-bold text-emerald-600 bg-emerald-500/10 border border-emerald-500/20 px-1 py-0.5 rounded whitespace-nowrap" x-text="'-' + gift.discount + '%'"></span>
                            </div>
                        </div>

                        <!-- Nhóm nút hành động: Mua ngay & Xem demo -->
                        <div class="grid grid-cols-2 gap-1.5 sm:gap-2 mt-2">
                            <!-- Nút Mua ngay -->
                            <button class="flex items-center justify-center gap-1 sm:gap-1.5 py-1.5 sm:py-2 px-1.5 sm:px-3 bg-primary hover:bg-primary/95 text-white rounded-xl text-[10px] sm:text-xs font-bold transition-all shadow-sm shadow-primary/10 active:scale-[0.97]">
                                <span class="material-symbols-outlined text-[14px] sm:text-[16px] select-none">credit_card</span>
                                <span>{{ __('Mua ngay') }}</span>
                            </button>
                            <!-- Nút Xem demo -->
                            <a :href="gift.demo_url" class="flex items-center justify-center gap-1 sm:gap-1.5 py-1.5 sm:py-2 px-1.5 sm:px-3 bg-app-surface hover:bg-app-main/5 border border-app-border text-app-text rounded-xl text-[10px] sm:text-xs font-bold transition-all active:scale-[0.97]">
                                <span class="material-symbols-outlined text-[14px] sm:text-[16px] select-none">visibility</span>
                                <span>{{ __('Xem demo') }}</span>
                            </a>
                        </div>

                        <!-- Liên kết hướng dẫn & video hướng dẫn -->
                        <div class="flex items-center justify-center gap-2 sm:gap-3 text-[10px] sm:text-xs mt-2 pt-2 border-t border-app-border/40">
                            <a :href="gift.guide_url" class="flex items-center gap-0.5 sm:gap-1 text-primary font-semibold whitespace-nowrap">
                                <span class="material-symbols-outlined text-[13px] sm:text-[15px] select-none">help</span>
                                <span>{{ __('Hướng dẫn') }}</span>
                            </a>
                            <span class="text-app-border select-none">|</span>
                            <a :href="gift.video_url" class="flex items-center gap-0.5 sm:gap-1 text-rose-500 font-semibold whitespace-nowrap">
                                <span class="material-symbols-outlined text-[13px] sm:text-[15px] select-none">play_circle</span>
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
    </div>
</x-app-layout>