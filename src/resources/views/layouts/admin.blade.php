<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title')</title>

<link rel="stylesheet"
href="{{ asset('css/admin.css') }}">
</head>

<body>

<header class="header">

<div class="header-logo">
<img src="{{ asset('images/logo.png') }}"
alt="COACHTECHロゴ">
</div>

<nav class="header-nav">

<a href="/admin/attendance/list">勤怠一覧</a>

<a href="/admin/staff/list">スタッフ一覧</a>

<a href="/admin/stamp_correction_request/list">
申請一覧
</a>

<form method="POST"
action="/admin/logout">
@csrf

<button type="submit"
class="logout-button">
ログアウト
</button>

</form>

</nav>

</header>

<main class="main">
@yield('content')
</main>

</body>
</html>