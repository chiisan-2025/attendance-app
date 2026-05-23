<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<header class="header">
    <div class="header-logo">
        <img src="{{ asset('images/logo.png') }}" alt="ロゴ">
    </div>
</header>

<main class="login-main">
    <div class="login-card">
        <h1 class="login-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
               @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-login">ログインする</button>

            <div class="auth-link-wrap">
                <a href="{{ url('/register') }}" class="auth-link">会員登録はこちら</a>
            </div>
        </form>
    </div>
</main>

</body>
</html>