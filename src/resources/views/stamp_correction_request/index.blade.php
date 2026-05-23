<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>申請一覧（一般ユーザー）</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<header class="header">
    <div class="header-logo">
        <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
    </div>

    <nav class="header-nav">
        <a href="{{ url('/attendance') }}">勤怠</a>
        <a href="{{ url('/attendance/list') }}">勤怠一覧</a>
        <a href="{{ url('/stamp_correction_request/list') }}">申請</a>

        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </nav>
</header>

<main class="main">
    <h1 class="page-title">申請一覧</h1>

    <div class="tabs">
        <a href="{{ url('/stamp_correction_request/list?status=承認待ち') }}"
           class="{{ $status === '承認待ち' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ url('/stamp_correction_request/list?status=承認済み') }}"
           class="{{ $status === '承認済み' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <table class="card-table">
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>

        @foreach ($requests as $request)
            <tr>
                <td>{{ $request->status }}</td>
                <td>{{ $request->user->name }}</td>
                <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                <td>{{ $request->reason }}</td>
                <td>{{ \Carbon\Carbon::parse($request->requested_at)->format('Y/m/d') }}</td>
                <td>
                    <a class="link-detail" href="{{ url('/attendance/detail/' . $request->attendance_id) }}">
                        詳細
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
</main>

</body>
</html>
