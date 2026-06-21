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
            { id: 1, title: '{{ __('Thiệp Sinh Nhật 3D') }}', category: 'birthday', uses: '1.2k', gradient: 'from-amber-400 via-orange-500 to-yellow-500', icon: 'cake', desc: '{{ __('Thiết kế hiện đại với nến lung linh và hiệu ứng bóng bay rực rỡ.') }}', tag: '{{ __('Sinh nhật') }}' },
            { id: 2, title: '{{ __('Thư Tình Yêu Ngọt Ngào') }}', category: 'love', uses: '3.4k', gradient: 'from-pink-500 via-rose-500 to-red-500', icon: 'favorite', desc: '{{ __('Bày tỏ lời yêu thương lãng mạn kèm nhạc nền và hiệu ứng trái tim rơi.') }}', tag: '{{ __('Tình yêu') }}' },
            { id: 3, title: '{{ __('Lời Cảm Ơn Chân Thành') }}', category: 'thank', uses: '890', gradient: 'from-teal-400 via-emerald-500 to-cyan-500', icon: 'volunteer_activism', desc: '{{ __('Thiết kế trang nhã, tinh tế giúp bạn bày tỏ lòng biết ơn sâu sắc.') }}', tag: '{{ __('Cảm ơn') }}' },
            { id: 4, title: '{{ __('Kỷ Niệm Ngày Chung Đôi') }}', category: 'anniversary', uses: '1.9k', gradient: 'from-violet-500 via-fuchsia-600 to-purple-650', icon: 'celebration', desc: '{{ __('Ghi lại dấu mốc thời gian đáng nhớ của hai người kèm thư viện ảnh.') }}', tag: '{{ __('Kỷ niệm') }}' },
            { id: 5, title: '{{ __('Giáng Sinh Ấm Áp') }}', category: 'christmas', uses: '2.4k', gradient: 'from-red-650 via-rose-700 to-emerald-700', icon: 'ac_unit', desc: '{{ __('Chúc mừng mùa Giáng Sinh với tuyết rơi nhẹ nhàng và nhạc Noel.') }}', tag: '{{ __('Lễ hội') }}' },
            { id: 6, title: '{{ __('Hộp Quà Bí Mật') }}', category: 'birthday', uses: '1.5k', gradient: 'from-yellow-400 via-amber-500 to-orange-500', icon: 'featured_seasonal', desc: '{{ __('Hộp quà ảo mở ra những điều bất ngờ đầy thú vị dành cho người nhận.') }}', tag: '{{ __('Sinh nhật') }}' },
            { id: 7, title: '{{ __('Chúc Ngủ Ngon') }}', category: 'love', uses: '760', gradient: 'from-slate-900 via-indigo-950 to-slate-900', icon: 'nights_stay', desc: '{{ __('Một lời chúc ngọt ngào trước khi chìm vào giấc ngủ với bầu trời sao.') }}', tag: '{{ __('Tình yêu') }}' },
            { id: 8, title: '{{ __('Chúc Mừng Năm Mới') }}', category: 'anniversary', uses: '3.1k', gradient: 'from-red-500 via-orange-500 to-yellow-500', icon: 'local_fire_department', desc: '{{ __('Lời chúc năm mới an khang thịnh vượng kèm hiệu ứng pháo hoa.') }}', tag: '{{ __('Kỷ niệm') }}' }
        ],
        get filteredGifts() {
            return this.gifts.filter(gift => {
                const matchesCategory = this.activeCategory === 'all' || gift.category === this.activeCategory;
                const matchesSearch = gift.title.toLowerCase().includes(this.searchQuery.toLowerCase()) || 
                                      gift.desc.toLowerCase().includes(this.searchQuery.toLowerCase());
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

                    {{-- Ảnh bìa mẫu (Gradient màu sắc + Icon) --}}
                    <div :class="gift.gradient"
                        class="relative w-full h-28 sm:h-36 bg-gradient-to-br flex items-center justify-center overflow-hidden">
                        {{-- Hiệu ứng bóng sáng phía sau --}}
                        <div
                            class="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                        </div>

                        {{-- Icon trung tâm --}}
                        <span
                            class="material-symbols-outlined text-[36px] sm:text-[48px] text-white/95 drop-shadow-md transform group-hover:scale-110 group-hover:rotate-6 transition-all duration-350 select-none"
                            x-text="gift.icon"></span>

                        {{-- Nhãn chủ đề (Tag) --}}
                        <span
                            class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-black/30 backdrop-blur-md text-[9px] sm:text-[10px] font-bold text-white uppercase tracking-wider select-none"
                            x-text="gift.tag"></span>

                        {{-- Lượt dùng --}}
                        <span
                            class="absolute top-2.5 right-2.5 px-1.5 py-0.5 rounded-lg bg-black/30 backdrop-blur-md text-[9px] sm:text-[10px] font-bold text-white/90 flex items-center gap-0.5 select-none">
                            <span
                                class="material-symbols-outlined text-[11px] sm:text-[12px] text-amber-400 fill-current">star</span>
                            <span x-text="gift.uses"></span>
                        </span>
                    </div>

                    {{-- Thông tin chi tiết --}}
                    <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between gap-2.5">
                        <div class="space-y-1">
                            <h3 class="text-xs sm:text-sm font-bold text-app-text line-clamp-1 group-hover:text-primary transition-colors duration-200"
                                x-text="gift.title"></h3>
                            <p class="text-[10px] sm:text-xs text-app-muted line-clamp-2 leading-normal"
                                x-text="gift.desc"></p>
                        </div>

                        {{-- Phần chân card --}}
                        <div class="flex items-center justify-between pt-2 border-t border-app-border/40 mt-auto">
                            <span class="text-[9px] sm:text-[11px] text-app-muted flex items-center gap-1 select-none">
                                <span class="material-symbols-outlined text-[12px] sm:text-[14px]">visibility</span>
                                {{ __('Xem thử') }}
                            </span>
                            <button
                                class="size-6 sm:size-8 rounded-full bg-primary hover:bg-primary/95 text-white flex items-center justify-center hover:scale-105 active:scale-95 transition-all shadow-sm shadow-primary/10">
                                <span class="material-symbols-outlined text-[14px] sm:text-[18px]">add</span>
                            </button>
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