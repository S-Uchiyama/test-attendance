@extends('layouts.app')

@section('title', 'ログイン')

@section('content')
    <section class="auth-card">
        <h1 class="auth-title">ログイン</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="auth-field">
                <label for="email" class="auth-label">メールアドレス</label>
                <input id="email" class="auth-input" type="email" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-field">
                <label for="password" class="auth-label">パスワード</label>
                <input id="password" class="auth-input" type="password" name="password">
                @error('password')
                    <p class="auth-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="auth-submit">ログインする</button>
        </form>

        <a href="{{ route('register') }}" class="auth-link">会員登録はこちら</a>
    </section>
@endsection
