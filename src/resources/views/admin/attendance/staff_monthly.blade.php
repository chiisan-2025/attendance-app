<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ別勤怠一覧（管理者）</title>
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
    <h1 class="page-title">{{ $staff->name }}さんの勤怠</h1>

    <div class="date-nav">
        <a href="{{ url('/admin/attendance/staff/' . $staff->id . '?month=' . $previousMonth) }}">← 前月</a>
        <span class="date-center">{{ $currentMonth->format('Y/m') }}</span>
        <a href="{{ url('/admin/attendance/staff/' . $staff->id . '?month=' . $nextMonth) }}">翌月 →</a>
    </div>

    <table class="card-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d') }}</td>
                    <td>{{ $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->total_break_time }}</td>
                    <td>{{ $attendance->total_work_time }}</td>
                    <td>
                        <a class="link-detail" href="{{ url('/admin/attendance/' . $attendance->id) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="csv-action">
    <a href="{{ url('/admin/attendance/staff/' . $staff->id . '/csv?month=' . $currentMonth->format('Y-m')) }}"
       class="btn-csv">
        CSV出力
    </a>
</div>
</main>

</body>
</html>