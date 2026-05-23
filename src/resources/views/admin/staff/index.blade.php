<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>スタッフ一覧（管理者）</title>
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
    <h1 class="page-title">スタッフ一覧</h1>

    <table class="card-table">
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>月次勤怠</th>
        </tr>

        @foreach ($staffs as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                <td>
                    <a class="link-detail" href="{{ url('/admin/attendance/staff/' . $staff->id) }}">
                        詳細
                    </a>
                </td>
            </tr>
        @endforeach
    </table>
</main>

</body>
</html>