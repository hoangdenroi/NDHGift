<x-app-layout :title="__('History - NDHGift')">
    {{-- ============================== --}}
    {{-- SECTION: Header trang giới thiệu --}}
    {{-- ============================== --}}
    <div class="flex items-center justify-between gap-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-app-text">{{ __('History') }}</h1>
            <p class="text-app-muted text-sm">{{ __('History of transactions') }}</p>
        </div>
        <!-- Breadcrumbs chỉ hiển thị trên desktop -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
            <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                NDHGift
            </a>
            <span class="material-symbols-outlined text-[14px] text-app-muted/40 select-none">chevron_right</span>
            <span class="text-app-text">{{ __('History') }}</span>
        </nav>
    </div>

    {{-- ============================== --}}
    {{-- SECTION: 2 cột chính --}}
    {{-- ============================== --}}

    </div>
</x-app-layout>