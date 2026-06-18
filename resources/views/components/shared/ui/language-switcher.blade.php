{{-- Component chuyển đổi ngôn ngữ — hiển thị trên header/navbar --}}
@php
    $supportedLocales = config('localization.supported_locales', []);
    $localeLabels = config('localization.locale_labels', []);
    $localeFlags = config('localization.locale_flags', []);
    $currentLocale = app()->getLocale();
@endphp

<div x-data="{ open: false }" @click.outside="open = false" class="relative">
    {{-- Nút hiển thị ngôn ngữ hiện tại --}}
    <button @click="open = !open" type="button"
        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
               text-slate-600 dark:text-slate-300
               hover:bg-slate-100 dark:hover:bg-slate-800
               transition-all duration-200 cursor-pointer select-none"
        aria-label="{{ __('Switch Language') }}">
        <span class="text-base leading-none">{{ $localeFlags[$currentLocale] ?? '🌐' }}</span>
        <span class="hidden sm:inline">{{ $localeLabels[$currentLocale] ?? strtoupper($currentLocale) }}</span>
        <span class="material-symbols-outlined text-[18px] transition-transform duration-200"
            :class="open ? 'rotate-180' : ''">expand_more</span>
    </button>

    {{-- Dropdown danh sách ngôn ngữ --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        x-cloak
        class="absolute right-0 mt-2 w-44 py-1.5
               bg-white dark:bg-[#1a2236]
               border border-slate-200 dark:border-slate-700
               rounded-xl shadow-xl shadow-slate-200/50 dark:shadow-black/30
               z-50 overflow-hidden">

        @foreach ($supportedLocales as $locale)
            @php
                $isActive = $locale === $currentLocale;
                $switchUrl = \App\Helpers\LocalizationHelper::switchLocaleUrl($locale);
            @endphp

            <a href="{{ $switchUrl }}"
                class="flex items-center gap-3 px-4 py-2.5 text-sm transition-colors duration-150
                       {{ $isActive
                           ? 'bg-blue-50 dark:bg-blue-900/20 text-primary font-semibold'
                           : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="text-base leading-none">{{ $localeFlags[$locale] ?? '🌐' }}</span>
                <span>{{ $localeLabels[$locale] ?? strtoupper($locale) }}</span>
                @if ($isActive)
                    <span class="material-symbols-outlined text-[16px] ml-auto text-primary">check</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
