<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修正申請承認画面（管理者）</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<header class="header">
    <div class="header-logo">
        <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
    </div>

    <nav class="header-nav">
        <a href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
        <a href="{{ url('/admin/staff/list') }}">スタッフ一覧</a>
        <a href="{{ url('/admin/stamp_correction_request/list') }}">申請一覧</a>

        <form method="POST" action="{{ url('/admin/logout') }}">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </nav>
</header>

<main class="main">
    <h1 class="page-title">勤怠詳細</h1>

    @php
        $break1 = $request->stampCorrectionRequestBreaks[0] ?? null;
        $break2 = $request->stampCorrectionRequestBreaks[1] ?? null;
    @endphp

    <div class="detail-card">

        <div class="detail-row">
            <div class="detail-label">名前</div>
            <div class="detail-value">{{ $request->user->name }}</div>
        </div>

        <div class="detail-row">
            <div class="detail-label">日付</div>
            <div class="detail-value detail-date">
                <span>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年') }}</span>
                <span>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('n月j日') }}</span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">出勤・退勤</div>
            <div class="detail-value detail-time-range">
                <span class="time-box">
                    {{ $request->requested_clock_in_at ? \Carbon\Carbon::parse($request->requested_clock_in_at)->format('H:i') : '' }}
                </span>
                <span class="time-separator">〜</span>
                <span class="time-box">
                    {{ $request->requested_clock_out_at ? \Carbon\Carbon::parse($request->requested_clock_out_at)->format('H:i') : '' }}
                </span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">休憩</div>
            <div class="detail-value detail-time-range">
                <span class="time-box">
                    {{ $break1 && $break1->requested_break_start_at ? \Carbon\Carbon::parse($break1->requested_break_start_at)->format('H:i') : '' }}
                </span>
                <span class="time-separator">〜</span>
                <span class="time-box">
                    {{ $break1 && $break1->requested_break_end_at ? \Carbon\Carbon::parse($break1->requested_break_end_at)->format('H:i') : '' }}
                </span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">休憩2</div>
            <div class="detail-value detail-time-range">
                <span class="time-box">
                    {{ $break2 && $break2->requested_break_start_at ? \Carbon\Carbon::parse($break2->requested_break_start_at)->format('H:i') : '' }}
                </span>
                <span class="time-separator">〜</span>
                <span class="time-box">
                    {{ $break2 && $break2->requested_break_end_at ? \Carbon\Carbon::parse($break2->requested_break_end_at)->format('H:i') : '' }}
                </span>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
                <div class="note-box">{{ $request->reason ?? '' }}</div>
            </div>
        </div>
    </div>

    <div class="detail-action">
        @if ($request->status === '承認待ち')
            <form method="POST" action="{{ url('/admin/stamp_correction_request/' . $request->id . '/approve') }}">
                @csrf
                <button type="submit" class="btn-detail-edit">承認</button>
            </form>
        @else
            <button type="button" class="btn-detail-edit" disabled>承認済</button>
        @endif
    </div>
</main>

</body>
</html>