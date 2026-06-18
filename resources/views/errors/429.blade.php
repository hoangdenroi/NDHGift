<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>429 - Quá nhiều yêu cầu</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #e2e8f0;
        }
        .container {
            text-align: center;
            max-width: 500px;
            padding: 2rem;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 800;
            background: linear-gradient(135deg, #f97316, #ef4444);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .message {
            font-size: 1.1rem;
            line-height: 1.6;
            color: #94a3b8;
            margin-bottom: 2rem;
        }
        .retry-info {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: rgba(249, 115, 22, 0.15);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 0.5rem;
            color: #fb923c;
            font-size: 0.9rem;
        }
        .back-link {
            display: block;
            margin-top: 1.5rem;
            color: #64748b;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover { color: #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">429</div>
        <p class="message">{{ $message ?? 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.' }}</p>
        <div class="retry-info">
            ⏱ Thử lại sau <strong>{{ $seconds ?? 60 }}</strong> giây
        </div>
        <a href="{{ url()->previous() }}" class="back-link">← Quay lại trang trước</a>
    </div>
</body>
</html>
