<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Món quà đang được hẹn giờ gửi ⏳ - NDHGift</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0b0f19;
            color: #f3f4f6;
        }
        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .countdown-num {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            background: linear-gradient(135deg, #3b82f6, #0d59f2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-lg w-full glass-panel rounded-3xl p-8 md:p-10 text-center space-y-8">
        <!-- Icon đồng hồ cát -->
        <div class="relative w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto border border-primary/20">
            <div class="absolute inset-0 bg-primary/5 rounded-full animate-ping duration-1000"></div>
            <span class="material-symbols-outlined text-[36px]">hourglass_empty</span>
        </div>
        
        <!-- Tiêu đề & Lời chúc trước -->
        <div class="space-y-3">
            <h1 class="text-xl md:text-2xl font-black font-outfit text-white leading-normal">
                Món Quà Đang Được Hẹn Giờ Gửi!
            </h1>
            <p class="text-slate-300 text-sm max-w-sm mx-auto leading-relaxed">
                Người gửi đã hẹn giờ mở khóa món quà đặc biệt này. Hãy kiên nhẫn đợi thêm một chút nhé!
            </p>
            @if(!empty($userGift->content_data['receiver_name']))
                <p class="text-xs text-slate-400">
                    Dành tặng cho: <span class="font-extrabold text-primary">{{ $userGift->content_data['receiver_name'] }}</span>
                </p>
            @endif
        </div>

        <!-- Bảng đồng hồ đếm ngược -->
        <div class="grid grid-cols-4 gap-2 md:gap-4 max-w-sm mx-auto pt-2">
            <!-- Ngày -->
            <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-3 flex flex-col items-center">
                <span id="days" class="text-2xl md:text-3xl countdown-num">00</span>
                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mt-1">Ngày</span>
            </div>
            <!-- Giờ -->
            <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-3 flex flex-col items-center">
                <span id="hours" class="text-2xl md:text-3xl countdown-num">00</span>
                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mt-1">Giờ</span>
            </div>
            <!-- Phút -->
            <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-3 flex flex-col items-center">
                <span id="minutes" class="text-2xl md:text-3xl countdown-num">00</span>
                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mt-1">Phút</span>
            </div>
            <!-- Giây -->
            <div class="bg-slate-900/50 border border-white/5 rounded-2xl p-3 flex flex-col items-center">
                <span id="seconds" class="text-2xl md:text-3xl countdown-num">00</span>
                <span class="text-[9px] uppercase tracking-wider text-slate-500 font-bold mt-1">Giây</span>
            </div>
        </div>

        <!-- Hẹn giờ mốc -->
        <div class="text-xs text-slate-400 border-t border-white/5 pt-6 flex flex-col items-center gap-1">
            <span>Thời điểm mở quà:</span>
            <span class="font-semibold text-slate-200">{{ $userGift->scheduled_at->format('H:i d/m/Y') }}</span>
        </div>
    </div>

    <!-- Script tính toán đếm ngược -->
    <script>
        // Mốc thời gian mở quà (timestamp miliseconds)
        const targetTime = {{ $userGift->scheduled_at->timestamp * 1000 }};

        function updateCountdown() {
            const now = new Date().getTime();
            const difference = targetTime - now;

            // Nếu đã qua mốc thời gian -> tự động reload trang để mở quà
            if (difference <= 0) {
                window.location.reload();
                return;
            }

            // Tính toán Ngày, Giờ, Phút, Giây
            const days = Math.floor(difference / (1000 * 60 * 60 * 24));
            const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((difference % (1000 * 60)) / 1000);

            // Hiển thị lên UI (luôn giữ 2 chữ số)
            document.getElementById('days').innerText = String(days).padStart(2, '0');
            document.getElementById('hours').innerText = String(hours).padStart(2, '0');
            document.getElementById('minutes').innerText = String(minutes).padStart(2, '0');
            document.getElementById('seconds').innerText = String(seconds).padStart(2, '0');
        }

        // Chạy lần đầu ngay lập tức
        updateCountdown();

        // Cập nhật mỗi giây
        const countdownInterval = setInterval(updateCountdown, 1000);
    </script>
</body>
</html>
