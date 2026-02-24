@extends('layouts.app')

@section('title', '会員登録')

@section('content')
    <section class="auth-card">
        <h1 class="auth-title">会員登録</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="auth-field">
                <label for="name" class="auth-label">名前</label>
                <input id="name" class="auth-input auth-input--register" type="text" name="name" value="{{ old('name') }}">
                @error('name')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="email" class="auth-label">メールアドレス</label>
                <input id="email" class="auth-input auth-input--register" type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password" class="auth-label">パスワード</label>
                <input id="password" class="auth-input auth-input--register" type="password" name="password">
                @error('password')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password_confirmation" class="auth-label">パスワード確認</label>
                <input id="password_confirmation" class="auth-input auth-input--register" type="password" name="password_confirmation">
            </div>

            <button type="submit" class="auth-submit">登録する</button>
        </form>

        <a href="{{ route('login') }}" class="auth-link">ログインはこちら</a>
    </section>
@endsection
