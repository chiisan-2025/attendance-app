<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠画面</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
    <header class="header">
        <div class="header-logo">
            <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
        </div>

        <nav class="header-nav">
            <a href="/attendance">勤怠</a>
            <a href="/attendance/list">勤怠一覧</a>
            <a href="/stamp_correction_request/list">申請</a>

            <form method="POST" action="/logout">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main class="attendance-main">
        <div class="status-badge">{{ $status }}</div>
        @php
            $weekDays = ['日', '月', '火', '水', '木', '金', '土'];
        @endphp
        <div class="attendance-date">{{ $now->format('Y年n月j日') }}({{ $weekDays[$now->dayOfWeek] }})
        </div>
        <div class="attendance-time">{{ $now->format('H:i') }}</div>

        @if (session('message'))
            <p class="attendance-message">{{ session('message') }}</p>
        @endif

        @if (!$attendance)
            {{-- 出勤前 --}}
            <form method="POST" action="/attendance/clock-in">
                @csrf
                <button type="submit">出勤</button>
            </form>

        @elseif ($attendance->status === '出勤中' && !$attendance->clock_out_at)
            {{-- 出勤中 --}}
            <div class="attendance-actions">
                <form method="POST" action="/attendance/clock-out">
                    @csrf
                    <button type="submit">退勤</button>
                </form>

                <form method="POST" action="/attendance/break-start">
                    @csrf
                    <button type="submit">休憩入</button>
                </form>
            </div>

        @elseif ($attendance->status === '休憩中' && !$attendance->clock_out_at)
            {{-- 休憩中 --}}
            <form method="POST" action="/attendance/break-end">
                @csrf
                <button type="submit">休憩戻</button>
            </form>

        @elseif ($attendance->clock_out_at)
            {{-- 退勤後 --}}
            <p class="attendance-message">お疲れ様でした。</p>
        @endif
    </main>
</body>
</html>