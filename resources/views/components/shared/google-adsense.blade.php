@props([
    'placement' => 'banner', // Các vị trí: popup, sidebar, footer, banner
    'slotId' => '1234567890', // Default slot ID của Google Adsense
])

@php
    $shouldShow = false;

    // Logic kiểm soát hiển thị dựa trên placement và adPercent từ InjectAdConfig middleware
    switch ($placement) {
        case 'popup':
            // Popup Ads chỉ hiển thị cho Bronze (adPercent >= 100)
            $shouldShow = ($adPercent >= 100);
            break;
        case 'sidebar':
            // Sidebar Ads hiển thị cho Silver trở xuống (adPercent >= 70)
            $shouldShow = ($adPercent >= 70);
            break;
        case 'footer':
            // Footer Ads hiển thị cho Gold trở xuống (adPercent >= 40)
            $shouldShow = ($adPercent >= 40);
            break;
        case 'banner':
        default:
            // Banner Ads nhỏ hiển thị cho Platinum trở xuống (adPercent >= 10)
            $shouldShow = ($adPercent >= 10);
            break;
    }
@endphp

@if($shouldShow)
    <div class="google-ad-container my-6 flex flex-col items-center justify-center p-4 bg-slate-50 dark:bg-slate-800/30 border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl relative overflow-hidden transition-all duration-300">
        {{-- Tag nhãn quảng cáo --}}
        <span class="absolute top-1.5 right-3 text-[10px] font-semibold text-slate-400 dark:text-slate-600 uppercase tracking-wider select-none pointer-events-none">
            {{ __('Advertisement') }}
        </span>

        {{-- Cấu hình hiển thị chi tiết theo placement --}}
        @if($placement === 'popup')
            <div class="ad-popup-mockup w-full max-w-sm p-4 text-center">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Google AdSense Pop-up</p>
                <div class="h-48 bg-slate-200 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400">
                    336x280 Large Rectangle
                </div>
            </div>
        @elseif($placement === 'sidebar')
            <div class="ad-sidebar-mockup w-full p-2 text-center">
                <div class="h-64 bg-slate-200 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400">
                    300x600 Half Page
                </div>
            </div>
        @elseif($placement === 'footer')
            <div class="ad-footer-mockup w-full text-center">
                <div class="h-24 bg-slate-200 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400">
                    728x90 Leaderboard
                </div>
            </div>
        @else
            {{-- Default banner --}}
            <div class="ad-banner-mockup w-full text-center">
                <div class="h-16 bg-slate-200 dark:bg-slate-800 rounded-lg flex items-center justify-center text-slate-400">
                    320x50 Mobile Banner
                </div>
            </div>
        @endif

        {{-- Mã Script thực tế của Google AdSense (Chạy trong môi trường live) --}}
        {{-- 
        <ins class="adsbygoogle"
             style="display:block"
             data-ad-client="ca-pub-{{ config('services.google_adsense.client_id', '123456789') }}"
             data-ad-slot="{{ $slotId }}"
             data-ad-format="auto"
             data-full-width-responsive="true"></ins>
        <script>
             (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
        --}}
    </div>
@else
    {{-- Hiển thị thông báo nhỏ nếu là Level cao được ẩn ads (Dành cho nhà phát triển hoặc SEO debug) --}}
    <!-- Quảng cáo ({{ $placement }}) đã được ẩn do cấp bậc tài khoản của bạn: {{ $userTier }} -->
@endif
