{{-- Component Hướng dẫn & Trợ giúp --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm" x-data="{ activeFaq: null }">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">Trung tâm trợ giúp</h2>
            <p class="text-sm text-app-muted mt-0.5">Tìm kiếm câu trả lời cho các thắc mắc thường gặp</p>
        </div>
    </div>
    
    <div class="p-6 space-y-6">
        {{-- Section 1: FAQ Accordion --}}
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-app-text flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-[20px] text-app-muted">live_help</span>
                Câu hỏi thường gặp (FAQ)
            </h3>
            
            <div class="space-y-2">
                {{-- FAQ 1 --}}
                <div class="border border-app-border rounded-xl overflow-hidden">
                    <button @click="activeFaq = activeFaq === 1 ? null : 1"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm text-app-text hover:bg-primary/5 transition-all">
                        <span>Làm thế nào để tạo một trang quà tặng mới?</span>
                        <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                            :class="activeFaq === 1 ? 'rotate-180 text-primary' : ''">keyboard_arrow_down</span>
                    </button>
                    <div x-show="activeFaq === 1" 
                        x-transition:enter="transition-all ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                        x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave="transition-all ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                        x-cloak class="px-5 pb-4 text-sm text-app-muted border-t border-app-border/50 pt-3">
                        Để tạo trang quà tặng, bạn chỉ cần ra trang chủ NDHGift, lựa chọn một template quà tặng ưng ý (Template Valentine, Sinh nhật, v.v.), cấu hình thông tin (lời chúc, hình ảnh) và bấm xuất bản. Hệ thống sẽ cấp cho bạn một URL riêng biệt.
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="border border-app-border rounded-xl overflow-hidden">
                    <button @click="activeFaq = activeFaq === 2 ? null : 2"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm text-app-text hover:bg-primary/5 transition-all">
                        <span>Số dư ví của tôi được nạp như thế nào?</span>
                        <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                            :class="activeFaq === 2 ? 'rotate-180 text-primary' : ''">keyboard_arrow_down</span>
                    </button>
                    <div x-show="activeFaq === 2" 
                        x-transition:enter="transition-all ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                        x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave="transition-all ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                        x-cloak class="px-5 pb-4 text-sm text-app-muted border-t border-app-border/50 pt-3">
                        Bạn có thể liên hệ trực tiếp với bộ phận chăm sóc khách hàng của NDHGift hoặc thực hiện chuyển khoản ngân hàng theo đúng cú pháp hiển thị trên trang thanh toán của NDHShop để được cộng số dư tự động trong vòng 1-3 phút.
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="border border-app-border rounded-xl overflow-hidden">
                    <button @click="activeFaq = activeFaq === 3 ? null : 3"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm text-app-text hover:bg-primary/5 transition-all">
                        <span>Tôi có thể tùy biến nhạc nền và hình ảnh của template không?</span>
                        <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                            :class="activeFaq === 3 ? 'rotate-180 text-primary' : ''">keyboard_arrow_down</span>
                    </button>
                    <div x-show="activeFaq === 3" 
                        x-transition:enter="transition-all ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                        x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave="transition-all ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                        x-cloak class="px-5 pb-4 text-sm text-app-muted border-t border-app-border/50 pt-3">
                        Hoàn toàn có thể. Mỗi template khi tạo đều cung cấp giao diện trực quan cho phép bạn tự upload hình ảnh cá nhân, chọn bài hát nhạc nền MP3 hoặc Youtube link, và thay đổi văn bản lời chúc cực kỳ dễ dàng.
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Section 2: Contact Support --}}
        <div class="pt-6 border-t border-app-border flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="space-y-0.5 text-center md:text-left">
                <h4 class="text-sm font-semibold text-app-text">Bạn vẫn cần sự giúp đỡ?</h4>
                <p class="text-xs text-app-muted">Bộ phận hỗ trợ kỹ thuật luôn sẵn sàng phục vụ bạn 24/7.</p>
            </div>
            <a href="{{ route('app.support.index', ['locale' => app()->getLocale()]) }}"
                class="h-10 px-5 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 flex items-center gap-2 active:scale-95">
                <span class="material-symbols-outlined text-[18px]">support_agent</span>
                Gửi yêu cầu hỗ trợ
            </a>
        </div>
    </div>
</div>
