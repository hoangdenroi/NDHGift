@php
    $languages = [
        ['code' => 'vi', 'short' => 'VN', 'name' => 'Tiếng Việt'],
        ['code' => 'en', 'short' => 'US', 'name' => 'Tiếng Anh'],
        // ['code' => 'zh', 'short' => 'CN', 'name' => 'Tiếng Trung'],
        // ['code' => 'ja', 'short' => 'JP', 'name' => 'Tiếng Nhật'],
    ];
@endphp

<div class="relative" x-data="{ 
    langOpen: false,
    currentLang: '{{ app()->getLocale() }}',
    async updateLang(newLang) {
        this.currentLang = newLang;
        this.langOpen = false;
        
        // Đồng bộ ngôn ngữ vào LocalStorage trước tiên
        localStorage.setItem('language', newLang);
        
        if (typeof lang !== 'undefined') lang = newLang;

        // Tính toán URL mới với mã ngôn ngữ đã chọn
        const pathParts = window.location.pathname.split('/');
        const currentLocale = pathParts[1];
        let newUrl = window.location.href;

        if (currentLocale && /^[a-z]{2}$/.test(currentLocale)) {
            pathParts[1] = newLang;
            newUrl = window.location.origin + pathParts.join('/') + window.location.search + window.location.hash;
        } else {
            newUrl = window.location.origin + '/' + newLang + window.location.search + window.location.hash;
        }

        // Nếu người dùng đã đăng nhập, gửi request cập nhật cấu hình ngôn ngữ ở DB
        if ({{ auth()->check() ? 'true' : 'false' }}) {
            try {
                await fetch('/api/v1/settings', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        key: 'language',
                        value: newLang
                    })
                });
            } catch (error) {
                console.error('Lỗi khi lưu cài đặt ngôn ngữ:', error);
            }
        }
        
        // Chuyển hướng trình duyệt sang URL ngôn ngữ mới
        window.location.href = newUrl;
    }
}">
    {{-- Button kích hoạt dropdown --}}
    <button @click="langOpen = !langOpen"
        class="relative flex size-8 sm:size-10 cursor-pointer items-center justify-center rounded-full text-slate-900 dark:text-white transition-colors"
        :class="langOpen ? 'bg-primary text-white' : 'hover:bg-slate-100 dark:hover:bg-slate-800'">
        <span class="material-symbols-outlined text-[20px] sm:text-[24px]">translate</span>
    </button>

    {{-- Backdrop trên mobile --}}
    <div x-show="langOpen" @click="langOpen = false" x-cloak class="sm:hidden fixed inset-0 z-[199]"></div>

    {{-- Dropdown Ngôn ngữ --}}
    <div x-show="langOpen" @click.outside="langOpen = false" @keydown.escape.window="langOpen = false"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed top-[56px] right-3 w-56 sm:absolute sm:top-[100%] sm:right-0 sm:mt-2 sm:w-64
               bg-app-surface rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] border border-app-border py-2 z-[200] origin-top-right" x-cloak>

        {{-- Mũi tên trỏ vào icon --}}
        <div x-ref="arrowOuter" x-effect="
                if(langOpen) {
                    $nextTick(() => {
                        let btn = $root.querySelector('button');
                        let btnRect = btn.getBoundingClientRect();
                        let btnCenter = btnRect.left + btnRect.width / 2;
                        let dropLeft = $el.parentElement.getBoundingClientRect().left;
                        $refs.arrowOuter.style.left = (btnCenter - dropLeft - 9) + 'px';
                        $refs.arrowInner.style.left = (btnCenter - dropLeft - 7) + 'px';
                    })
                }
            "
            class="absolute -top-[9px] w-0 h-0 border-l-[9px] border-l-transparent border-r-[9px] border-r-transparent border-b-[9px] border-b-app-border">
        </div>
        <div x-ref="arrowInner"
            class="absolute -top-[7px] w-0 h-0 border-l-[7px] border-l-transparent border-r-[7px] border-r-transparent border-b-[7px] border-b-app-surface">
        </div>

        {{-- Danh sách ngôn ngữ --}}
        <div class="flex flex-col">
            @foreach ($languages as $l)
                <button @click="updateLang('{{ $l['code'] }}')"
                    class="flex items-center gap-4 px-5 py-3.5 hover:bg-app-main transition-colors w-full text-left group">
                    <div class="w-6 shrink-0 flex items-center justify-center">
                        <img src="{{ asset('assets/images/flags/' . strtolower($l['short']) . '.png') }}"
                            alt="{{ $l['name'] }}" class="w-5 h-3.5 object-cover shadow-sm rounded-[2px]">
                    </div>
                    <span class="flex-1 text-[13px] font-semibold text-app-text group-hover:text-primary transition-colors"
                        :class="currentLang === '{{ $l['code'] }}' ? 'text-primary' : ''">
                        {{ $l['name'] }}
                    </span>
                    <span x-show="currentLang === '{{ $l['code'] }}'"
                        class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span>
                </button>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function () {
        // Đồng bộ locale khi người dùng nhấn nút Back/Forward của trình duyệt (hỗ trợ cả bfcache)
        window.addEventListener('pageshow', function (event) {
            const savedLang = localStorage.getItem('language');
            if (!savedLang) return;

            const pathParts = window.location.pathname.split('/');
            const currentLocale = pathParts[1];

            // Nếu URL locale khác với ngôn ngữ đã lưu trong LocalStorage, tiến hành chuyển hướng
            if (currentLocale && /^[a-z]{2}$/.test(currentLocale) && currentLocale !== savedLang) {
                pathParts[1] = savedLang;
                const newUrl = window.location.origin + pathParts.join('/') + window.location.search + window.location.hash;
                window.location.replace(newUrl);
            }
        });
    })();
</script>