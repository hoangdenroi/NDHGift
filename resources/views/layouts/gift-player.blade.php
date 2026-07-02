<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO & Open Graph Tags động --}}
    @php
        $title = $giftData['title'] ?? 'Món quà ý nghĩa gửi tặng bạn 💖';
        $description = $giftData['message'] ?? 'Hãy mở ra để khám phá điều bất ngờ dành riêng cho bạn nhé!';
        $previewUrl = $giftTemplate['preview'] ?? asset('assets/images/gifts/heart_3d.png');
        $receiver = $giftData['receiver_name'] ?? '';
        $sender = $giftData['sender_name'] ?? '';
        $pageTitle = $receiver ? "Quà gửi tặng {$receiver} 💖" : "Món quà ý nghĩa 💖";
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ Str::limit($description, 150) }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ Str::limit($description, 150) }}">
    <meta property="og:image" content="{{ $previewUrl }}">
    <meta property="og:site_name" content="NDHGift">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ Str::limit($description, 150) }}">
    <meta name="twitter:image" content="{{ $previewUrl }}">

    <!-- Google Fonts & Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;600;800;900&family=Playfair+Display:ital,wght@0,600;0,800;1,600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    {{-- Import CSS & JS từ asset pipeline của Laravel --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* CSS tuỳ chỉnh cho trải nghiệm mượt mà của Player */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #000000;
            color: #f3f4f6;
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            user-select: none;
            -webkit-user-select: none;
        }

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        .font-playfair {
            font-family: 'Playfair Display', serif;
        }

        /* Hiệu ứng Glassmorphism */
        .glass-panel {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        /* Đĩa nhạc xoay */
        .cd-rotate {
            animation: spin 8s linear infinite;
        }

        .cd-paused {
            animation-play-state: paused;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Hiệu ứng Sóng nhạc (Audio Visualizer giả lập) */
        .music-bar {
            width: 3px;
            height: 4px;
            background-color: var(--color-primary, #0d59f2);
            border-radius: 2px;
            animation: soundWave 1.2s ease-in-out infinite alternate;
        }

        @keyframes soundWave {
            0% {
                height: 4px;
            }

            100% {
                height: 24px;
            }
        }

        .music-bar:nth-child(2) {
            animation-delay: 0.15s;
        }

        .music-bar:nth-child(3) {
            animation-delay: 0.3s;
        }

        .music-bar:nth-child(4) {
            animation-delay: 0.45s;
        }

        /* Animation cho các phần tử */
        .fade-out {
            opacity: 0;
            pointer-events: none;
            transition: opacity 1.2s ease;
        }

        /* Đổ bóng cho text */
        .text-glow {
            text-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
        }

        .text-glow-primary {
            text-shadow: 0 0 15px rgba(13, 89, 242, 0.4);
        }

        /* Ẩn scrollbar */
        ::-webkit-scrollbar {
            display: none;
        }

        /* Progress Bar cho Auto Load */
        .progress-container {
            width: 240px;
            height: 5px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .progress-bar-fill {
            height: 100%;
            width: 0%;
            background: linear-gradient(to right, #ec4899, #f43f5e);
            /* Hồng đỏ ngọt ngào */
            border-radius: 4px;
            transition: width 0.08s linear;
        }

        .theme-blue .progress-bar-fill {
            background: linear-gradient(to right, #38bdf8, #0d59f2);
            /* Xanh tuyết */
        }

        /* Hiệu ứng bay lên */
        .floating-icon {
            animation: floatUp 3s infinite ease-in-out;
        }

        @keyframes floatUp {
            0% {
                transform: translateY(20px) scale(0.8);
                opacity: 0;
            }

            50% {
                transform: translateY(-10px) scale(1.1);
                opacity: 0.9;
            }

            100% {
                transform: translateY(-40px) scale(0.8);
                opacity: 0;
            }
        }

        /* Press Hold Button */
        .hold-btn-wrapper {
            position: absolute;
            bottom: 12%;
            left: 50%;
            transform: translateX(-50%);
            z-index: 110;
        }

        .hold-btn-circle {
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), background-color 0.2s ease, border-color 0.2s ease;
        }

        .hold-btn-active {
            transform: scale(1.15) !important;
            background-color: rgba(255, 255, 255, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
        }
    </style>

    {{-- Script SDK khai báo trước để template con sử dụng --}}
    <script>
        window.giftData = @json($giftData);
        window.isDemo = {{ ($isDemo ?? false) ? 'true' : 'false' }};

        window.NDHGift = {
            isReady: false,
            _callbacks: [],
            onReady(callback) {
                if (this.isReady) {
                    try {
                        callback(window.giftData);
                    } catch (e) {
                        console.error("Lỗi khởi chạy hiệu ứng 3D:", e);
                    }
                } else {
                    this._callbacks.push(callback);
                }
            },
            triggerReady() {
                this.isReady = true;
                this._callbacks.forEach(cb => {
                    try {
                        cb(window.giftData);
                    } catch (e) {
                        console.error("Lỗi khởi chạy hiệu ứng 3D trong callback:", e);
                    }
                });
            }
        };
    </script>

    @yield('head')
</head>

<body class="h-full w-full relative">

    {{-- WATERMARK DEMO (Hiển thị khi ở chế độ xem thử) --}}
    @if($isDemo ?? false)
        <div class="fixed inset-0 flex items-center justify-center pointer-events-none select-none z-[1] overflow-hidden">
            <span class="text-[8vw] md:text-[6vw] lg:text-[5vw] font-black text-white/5 tracking-widest rotate-[-45deg] uppercase font-outfit select-none whitespace-nowrap">{{ 'DEMO MODE' }}</span>
        </div>
    @endif

    {{-- 3D BACKGROUND CANVAS CONTAINER --}}
    <div id="canvas-container" class="absolute inset-0 z-0"></div>

    {{-- 1. LOADING SCREEN / OPENING SCREEN --}}
    @php
        $openingType = $giftTemplate->opening_type ?? 'auto_load';
        $themeClass = ($giftTemplate->code === 'winter_3d') ? 'theme-blue' : 'theme-rose';
        $loadingTitle = ($giftTemplate->code === 'heart_3d') ? 'Đang chuẩn bị yêu thương...' : 'Đang chuẩn bị quà tặng...';
    @endphp

    <div id="opening-screen"
        class="fixed inset-0 z-[100] flex flex-col items-center justify-center transition-all duration-1000 ease-out {{ $themeClass }} {{ $openingType === 'press_hold' ? 'bg-black/30 backdrop-blur-[2px]' : 'bg-slate-950/95 backdrop-blur-md' }}">

        @if($openingType === 'auto_load')
            {{-- CHẾ ĐỘ 1: AUTO LOAD --}}
            <div class="max-w-md w-full px-6 flex flex-col items-center space-y-6 animate-fade-in text-center">
                <!-- Tiêu đề lãng mạn -->
                <h2 class="text-xl font-bold font-outfit tracking-wide text-glow"
                    style="color: {{ $giftTemplate->code === 'heart_3d' ? '#f43f5e' : '#0d59f2' }}; text-shadow: 0 0 15px rgba(244, 63, 94, 0.2)">
                    {{ $loadingTitle }}
                </h2>

                <!-- Icon nổi hoặc bay lên -->
                <div class="relative w-20 h-20 flex items-center justify-center select-none pointer-events-none">
                    @if($giftTemplate->code === 'heart_3d')
                        <span class="absolute text-5xl floating-icon" style="animation-delay: 0s;">💖</span>
                        <span class="absolute text-3xl floating-icon"
                            style="animation-delay: 1.5s; left: 10px; top: 10px;">💕</span>
                    @else
                        <span class="absolute text-5xl floating-icon" style="animation-delay: 0s;">❄️</span>
                        <span class="absolute text-3xl floating-icon"
                            style="animation-delay: 1.5s; left: 10px; top: 10px;">❅</span>
                    @endif
                </div>

                <div class="flex flex-col items-center space-y-2.5">
                    <span class="text-[10px] font-extrabold tracking-widest text-slate-400 uppercase">ĐANG CHUẨN BỊ QUÀ
                        TẶNG...</span>
                    <!-- Thanh tiến trình loading -->
                    <div class="progress-container">
                        <div id="loading-progress-bar" class="progress-bar-fill"></div>
                    </div>
                    <!-- Số phần trăm -->
                    <span id="loading-progress-text" class="text-xs font-black text-slate-300 font-outfit">0%</span>
                </div>
            </div>
        @else
            {{-- CHẾ ĐỘ 2: PRESS HOLD --}}
            <div class="hold-btn-wrapper flex flex-col items-center space-y-4 pointer-events-auto">
                <span class="text-xs font-extrabold tracking-widest uppercase text-glow animate-pulse"
                    style="color: {{ $giftTemplate->code === 'heart_3d' ? '#ff85a2' : '#7dd3fc' }}; text-shadow: 0 0 10px {{ $giftTemplate->code === 'heart_3d' ? 'rgba(255,133,162,0.4)' : 'rgba(125,211,252,0.4)' }}">
                    Giữ để bắt đầu
                </span>

                <div class="relative w-24 h-24 flex items-center justify-center cursor-pointer select-none"
                    id="btn-hold-trigger">
                    <!-- SVG tiến trình viền tròn -->
                    <svg class="absolute inset-0 w-full h-full transform -rotate-90">
                        <circle cx="48" cy="48" r="42" stroke="rgba(255,255,255,0.15)" stroke-width="4"
                            fill="transparent" />
                        <circle id="hold-progress-circle" cx="48" cy="48" r="42"
                            stroke="{{ $giftTemplate->code === 'heart_3d' ? '#f43f5e' : '#0d59f2' }}" stroke-width="4"
                            fill="transparent" stroke-linecap="round" stroke-dasharray="264" stroke-dashoffset="264"
                            style="transition: stroke-dashoffset 0.05s linear;" />
                    </svg>
                    <!-- Nút tròn trung tâm -->
                    <div
                        class="w-16 h-16 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 flex items-center justify-center text-white transition-all active:scale-95 shadow-lg hold-btn-circle">
                        <span class="material-symbols-outlined text-[28px] animate-pulse">touch_app</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- 2. CONTROL CENTER (SETTINGS MENU) --}}
    <div id="control-center" class="fixed top-4 right-4 z-50"
        x-data="{ open: false, isMuted: false, isPlaying: false, isFullscreen: false, isOverlayVisible: true }">
        <!-- Nút Settings -->
        <button @click="open = !open"
            class="w-10 h-10 rounded-full glass-panel flex items-center justify-center text-white hover:bg-white/10 active:scale-95 transition-all outline-none"
            title="Cài đặt">
            <span class="material-symbols-outlined text-[20px] transition-transform duration-300"
                :class="open ? 'rotate-90' : ''">settings</span>
        </button>

        <!-- Panel Cài đặt -->
        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="absolute right-0 mt-3 w-56 rounded-2xl glass-panel p-4 space-y-4" style="display: none;">

            <!-- Widget Nhạc nền -->
            <div class="space-y-2.5 pb-2.5 border-b border-white/10">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Nhạc Nền</span>
                <div class="flex items-center gap-3">
                    <!-- Đĩa nhạc -->
                    <div class="w-10 h-10 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center cd-rotate"
                        :class="isPlaying ? '' : 'cd-paused'">
                        <span class="material-symbols-outlined text-[18px] text-primary select-none">music_note</span>
                    </div>
                    <!-- Control nhạc -->
                    <div class="flex-1 flex items-center justify-between">
                        <button @click="togglePlayMusic(); isPlaying = !isPlaying"
                            class="text-xs font-bold text-white hover:text-primary transition-colors">
                            <span x-text="isPlaying ? 'Tạm Dừng' : 'Phát Nhạc'">Tạm Dừng</span>
                        </button>
                        <!-- Sóng nhạc -->
                        <div class="flex items-end gap-0.5 h-6 overflow-hidden" x-show="isPlaying">
                            <div class="music-bar"></div>
                            <div class="music-bar"></div>
                            <div class="music-bar"></div>
                            <div class="music-bar"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Các nút điều khiển khác -->
            <div class="space-y-1">
                <!-- Toggle Lời chúc -->
                <button @click="toggleOverlay(); isOverlayVisible = !isOverlayVisible"
                    class="w-full flex items-center justify-between px-2 py-2 rounded-xl hover:bg-white/5 text-slate-200 text-xs font-semibold transition-colors">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]"
                            x-text="isOverlayVisible ? 'visibility_off' : 'visibility'">visibility_off</span>
                        <span x-text="isOverlayVisible ? 'Ẩn lời chúc' : 'Hiện lời chúc'">Ẩn lời chúc</span>
                    </span>
                </button>

                <!-- Toggle Fullscreen -->
                <button @click="toggleFullscreen(); isFullscreen = !isFullscreen"
                    class="w-full flex items-center justify-between px-2 py-2 rounded-xl hover:bg-white/5 text-slate-200 text-xs font-semibold transition-colors">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]"
                            x-text="isFullscreen ? 'fullscreen_exit' : 'fullscreen'">fullscreen</span>
                        <span x-text="isFullscreen ? 'Thu nhỏ màn hình' : 'Toàn màn hình'">Toàn màn hình</span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- 3. TEXT OVERLAY (CARD LỜI CHÚC) --}}
    <div id="text-overlay"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] max-w-md z-30 transition-all duration-700 ease-in-out">
        <div class="glass-panel rounded-3xl p-6 relative flex flex-col gap-4 text-center select-text">

            <!-- Nút đóng nhanh card lời chúc -->
            <button onclick="hideOverlay()"
                class="absolute top-4 right-4 w-7 h-7 rounded-full bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-colors"
                title="Ẩn lời chúc">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>

            <!-- Nội dung lời chúc -->
            <div class="space-y-3.5">
                <!-- Tiêu đề quà tặng -->
                @if($title)
                    <h1 class="text-base font-extrabold font-outfit text-primary tracking-wide text-glow-primary uppercase">
                        {{ $title }}
                    </h1>
                @endif

                <!-- Tên người nhận -->
                @if($receiver)
                    <h2 class="text-2xl font-black font-outfit text-white text-glow">
                        {{ $receiver }}
                    </h2>
                @endif

                <!-- Nội dung lời nhắn -->
                <p class="text-slate-200 text-xs font-medium leading-relaxed font-inter px-2 max-h-36 overflow-y-auto">
                    {!! nl2br(e($description)) !!}
                </p>

                <!-- Tên người gửi -->
                @if($sender)
                    <div class="pt-2 border-t border-white/5 flex flex-col items-center gap-0.5">
                        <span class="text-[9px] uppercase tracking-widest text-slate-400">Gửi từ</span>
                        <span class="text-sm font-black font-outfit text-pink-300">
                            {{ $sender }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 4. ICON PHONG THƯ THU GỌN --}}
    <div id="envelope-widget"
        class="fixed bottom-6 left-6 z-40 transition-all duration-500 transform translate-y-20 opacity-0">
        <button onclick="showOverlay()"
            class="w-12 h-12 rounded-full bg-gradient-to-tr from-primary to-blue-500 border border-white/20 flex items-center justify-center text-white shadow-lg shadow-primary/30 hover:shadow-primary/50 active:scale-95 transition-all group relative"
            title="Xem lời chúc">
            <span class="absolute inset-0 rounded-full bg-primary/20 animate-ping duration-1000"></span>
            <span class="material-symbols-outlined text-[22px] group-hover:scale-110 transition-transform">mail</span>
        </button>
    </div>

    {{-- AUDIO PLAYER --}}
    @if(!empty($giftData['settings']['music_url']))
        <audio id="bg-music" src="{{ $giftData['settings']['music_url'] }}" loop preload="auto" class="hidden"></audio>
    @elseif(!empty($giftTemplate->form_schema['fields']))
        @php
            // Lấy default music url từ form schema nếu có
            $musicField = collect($giftTemplate->form_schema['fields'])->firstWhere('type', 'music')
                ?? collect($giftTemplate->form_schema['fields'])->firstWhere('name', 'music_url');
            $defaultMusic = $musicField['default'] ?? 'https://assets.mixkit.co/music/preview/mixkit-beautiful-dream-493.mp3';
        @endphp
        <audio id="bg-music" src="{{ $defaultMusic }}" loop preload="auto" class="hidden"></audio>
    @else
        <audio id="bg-music" src="https://assets.mixkit.co/music/preview/mixkit-beautiful-dream-493.mp3" loop preload="auto"
            class="hidden"></audio>
    @endif

    {{-- CORE AUDIO & INTERACTION LOGIC --}}
    <script>
        const audio = document.getElementById('bg-music');
        const openingScreen = document.getElementById('opening-screen');
        const textOverlay = document.getElementById('text-overlay');
        const envelopeWidget = document.getElementById('envelope-widget');

        // Hàm phát nhạc nền
        function playMusic() {
            if (audio) {
                audio.play()
                    .then(() => {
                        updateWidgetState(true);
                    })
                    .catch(err => {
                        console.log("Autoplay bị trình duyệt ngăn cản:", err);
                        updateWidgetState(false);
                    });
            }
        }

        // Hàm tạm dừng nhạc
        function pauseMusic() {
            if (audio) {
                audio.pause();
                updateWidgetState(false);
            }
        }

        // Cập nhật trạng thái hiển thị của Control Center Widget qua Alpine
        function updateWidgetState(isPlaying) {
            // Đồng bộ trạng thái vào x-data của Alpine nếu có
            const AlpineEl = document.getElementById('control-center');
            if (AlpineEl && AlpineEl.__x) {
                AlpineEl.__x.$data.isPlaying = isPlaying;
            }
        }

        // Click nút Settings để điều khiển nhạc
        function togglePlayMusic() {
            if (audio) {
                if (audio.paused) {
                    audio.play().then(() => updateWidgetState(true));
                } else {
                    audio.pause();
                    updateWidgetState(false);
                }
            }
        }

        window.NDHGift.openingType = "{{ $openingType }}";
        window.NDHGift.isOpened = false;

        // Mở Quà 🎁
        function openGift() {
            if (window.NDHGift.isOpened) return;
            window.NDHGift.isOpened = true;

            // 1. Chạy nhạc nền (Bypass Autoplay)
            playMusic();

            // 2. Ẩn màn hình Loading
            openingScreen.classList.add('fade-out');
            setTimeout(() => {
                openingScreen.style.display = 'none';
            }, 1000);

            // 3. Trigger SDK callback chạy hiệu ứng 3D
            window.NDHGift.triggerReady();
        }

        // Logic Auto Load
        if (window.NDHGift.openingType === 'auto_load') {
            let progress = 0;
            const progressBar = document.getElementById('loading-progress-bar');
            const progressText = document.getElementById('loading-progress-text');

            const interval = setInterval(() => {
                progress += Math.floor(Math.random() * 4) + 2; // Tăng ngẫu nhiên 2-5%
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    setTimeout(() => {
                        openGift();
                    }, 400);
                }
                if (progressBar) progressBar.style.width = progress + '%';
                if (progressText) progressText.innerText = progress + '%';
            }, 80);
        }

        // Logic Press Hold
        if (window.NDHGift.openingType === 'press_hold') {
            const btnHold = document.getElementById('btn-hold-trigger');
            const progressCircle = document.getElementById('hold-progress-circle');
            const innerBtn = btnHold ? btnHold.querySelector('.hold-btn-circle') : null;
            let holdProgress = 0;
            let holdInterval = null;
            let releaseInterval = null;
            const maxOffset = 264;

            function updateCircle() {
                if (progressCircle) {
                    const offset = maxOffset - (holdProgress / 100) * maxOffset;
                    progressCircle.style.strokeDashoffset = offset;
                }
            }

            function startHolding(e) {
                if (e) e.preventDefault();
                if (window.NDHGift.isOpened) return;

                clearInterval(releaseInterval);
                if (innerBtn) innerBtn.classList.add('hold-btn-active');

                holdInterval = setInterval(() => {
                    holdProgress += 2.5; // Mất 1.2 giây để nạp đầy (40 * 30ms)
                    if (holdProgress >= 100) {
                        holdProgress = 100;
                        clearInterval(holdInterval);
                        if (innerBtn) innerBtn.classList.remove('hold-btn-active');
                        openGift();
                    }
                    updateCircle();
                }, 30);
            }

            function stopHolding() {
                clearInterval(holdInterval);
                if (innerBtn) innerBtn.classList.remove('hold-btn-active');
                if (window.NDHGift.isOpened) return;

                // Xả năng lượng nhanh về 0
                releaseInterval = setInterval(() => {
                    holdProgress -= 5;
                    if (holdProgress <= 0) {
                        holdProgress = 0;
                        clearInterval(releaseInterval);
                    }
                    updateCircle();
                }, 20);
            }

            if (btnHold) {
                // Sự kiện chuột
                btnHold.addEventListener('mousedown', startHolding);
                btnHold.addEventListener('mouseup', stopHolding);
                btnHold.addEventListener('mouseleave', stopHolding);

                // Sự kiện cảm ứng mobile
                btnHold.addEventListener('touchstart', startHolding, { passive: false });
                btnHold.addEventListener('touchend', stopHolding);
                btnHold.addEventListener('touchcancel', stopHolding);
            }
        }

        // Ẩn lớp phủ lời chúc
        function hideOverlay() {
            textOverlay.classList.add('translate-y-20', 'opacity-0');
            textOverlay.style.pointerEvents = 'none';

            // Hiện icon phong thư
            setTimeout(() => {
                envelopeWidget.classList.remove('translate-y-20', 'opacity-0');
            }, 300);

            // Đồng bộ trạng thái Settings
            const AlpineEl = document.getElementById('control-center');
            if (AlpineEl && AlpineEl.__x) {
                AlpineEl.__x.$data.isOverlayVisible = false;
            }
        }

        // Hiện lớp phủ lời chúc
        function showOverlay() {
            // Ẩn icon phong thư
            envelopeWidget.classList.add('translate-y-20', 'opacity-0');

            // Hiện card lời chúc
            setTimeout(() => {
                textOverlay.classList.remove('translate-y-20', 'opacity-0');
                textOverlay.style.pointerEvents = 'auto';
            }, 200);

            // Đồng bộ trạng thái Settings
            const AlpineEl = document.getElementById('control-center');
            if (AlpineEl && AlpineEl.__x) {
                AlpineEl.__x.$data.isOverlayVisible = true;
            }
        }

        // Hàm toggel gọi từ Settings
        function toggleOverlay() {
            const isVisible = !textOverlay.classList.contains('opacity-0');
            if (isVisible) {
                hideOverlay();
            } else {
                showOverlay();
            }
        }

        // Toggle Fullscreen
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log(`Lỗi khi vào chế độ toàn màn hình: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>

    {{-- RENDER HIỆU ỨNG 3D CON --}}
    @yield('effect-content')

</body>

</html>