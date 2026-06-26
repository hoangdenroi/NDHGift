{{-- Thanh tìm kiếm: Desktop (ô input) + Mobile/Tablet (icon + dropdown) --}}

{{-- Desktop search --}}
<label class="hidden lg:flex flex-col min-w-40 h-9 max-w-xs flex-1">
    <div
        class="flex w-full flex-1 items-center rounded-lg h-full bg-app-surface border border-app-border focus-within:ring-2 focus-within:ring-primary/20 transition-all">
        <div class="text-app-muted flex border-none items-center justify-center pl-3 rounded-l-lg">
            <span class="material-symbols-outlined text-[20px]">search</span>
        </div>
        <input
            class="flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-app-text focus:outline-0 focus:ring-0 border-none bg-transparent h-full placeholder:text-app-muted px-3 pl-1.5 text-sm font-normal leading-none"
            placeholder="Tìm kiếm..." value="" />
    </div>
</label>

{{-- Mobile search dropdown --}}
<div class="relative lg:hidden" x-data="{ searchOpen: false }">
    <button @click="searchOpen = !searchOpen"
        class="flex size-8 sm:size-10 cursor-pointer items-center justify-center rounded-full text-app-text transition-colors"
        :class="searchOpen ? 'bg-primary text-white' : 'hover:bg-app-surface'">
        <span class="material-symbols-outlined text-[20px] sm:text-[24px]">search</span>
    </button>
    {{-- Dropdown search --}}
    <div x-show="searchOpen" @click.outside="searchOpen = false" @keydown.escape.window="searchOpen = false"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
        style="position: fixed; top: 56px; right: 12px; transform: none; width: min(calc(100vw - 24px), 500px); min-width: 280px;"
        class="bg-app-surface rounded-xl shadow-xl border border-app-border p-4 z-[200]" x-cloak>
        {{-- Mũi tên chỉ lên icon --}}
        <div x-ref="arrowOuter" x-effect="
                if(searchOpen) {
                    $nextTick(() => {
                        let btn = $root.querySelector('button');
                        let btnCenter = btn.getBoundingClientRect().left + btn.offsetWidth / 2;
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
        {{-- Ô input --}}
        <div class="relative">
            <span
                class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-app-muted text-[20px]">search</span>
            <input x-ref="searchInput" @keydown.enter="window.location.href='/search?q='+$refs.searchInput.value"
                x-init="$watch('searchOpen', value => { if(value) $nextTick(() => $refs.searchInput.focus()) })"
                class="w-full pl-10 pr-4 py-2.5 bg-app-main border border-app-border rounded-lg text-sm text-app-text placeholder:text-app-muted focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition-all"
                placeholder="Tìm kiếm..." type="text" />
        </div>
    </div>
</div>