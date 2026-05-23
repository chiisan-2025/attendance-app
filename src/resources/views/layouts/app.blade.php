<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>@yield('title')</title>

    <link rel="stylesheet"
          href="{{ asset('css/admin.css') }}">
</head>

<body>

    <header class="header">

        <div class="header-logo">
            <img src="{{ asset('images/logo.png') }}"
                 alt="ロゴ">
        </div>

        @if (
            !request()->is('email/verify') &&
            !request()->is('login') &&
            !request()->is('register') &&
            !request()->is('admin/login')
        )
            <nav class="header-nav">
                <a href="/attendance">勤怠</a>
                <a href="/attendance/list">勤怠一覧</a>
                <a href="/stamp_correction_request/list">申請</a>

                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            </nav>
        @endif

    </header>

    <main>
        @yield('content')
    </main>

</body>
</html>