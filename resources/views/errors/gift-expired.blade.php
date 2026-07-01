<!DOCTYPE html>
<html lang="vi" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Món quà đã hết hạn ⏳ - NDHGift</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
    </style>
</head>

<body class="h-full flex items-center justify-center p-4">
    <div class="max-w-md w-full glass-panel rounded-3xl p-8 text-center space-y-6">
        <div
            class="w-20 h-20 bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center mx-auto border border-rose-500/20">
            <span class="material-symbols-outlined text-[40px] animate-pulse">hourglass_disabled</span>
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl font-black font-outfit text-white">Món Quà Đã Hết Hạn</h1>
            <p class="text-slate-400 text-sm leading-relaxed">
                Món quà ý nghĩa này đã hoàn thành sứ mệnh gửi trao yêu thương và hết thời gian hiển thị vào lúc <span
                    class="text-rose-400 font-semibold">{{ $userGift->expires_at->format('H:i d/m/Y') }}</span>.
            </p>
        </div>

        <div class="pt-4 border-t border-white/5 space-y-3">
            <p class="text-xs text-slate-400">Bạn muốn tạo một món quà bất ngờ khác gửi tặng người thân yêu?</p>
            <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}"
                class="inline-flex items-center justify-center gap-2 w-full py-3 bg-primary hover:bg-primary/95 text-white font-extrabold text-xs tracking-wider uppercase rounded-xl transition-all shadow-md shadow-primary/20">
                <span class="material-symbols-outlined text-[16px]">celebration</span>
                Tạo quà tặng mới
            </a>
        </div>
    </div>
</body>

</html>