@extends('layouts.app')

@section('title','管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<main class="main login-main">
    <div class="login-card">
        <h1 class="login-title">管理者ログイン</h1>

        <form method="POST" action="/admin/login">
            @csrf

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}">
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password">
            </div>

            <button type="submit" class="btn-login">
                管理者ログインする
            </button>
        </form>
    </div>
</main>
@endsection