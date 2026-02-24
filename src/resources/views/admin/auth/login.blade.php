@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')
    <section class="auth-card">
        <h1 class="auth-title">管理者ログイン</h1>

        <form method="POST" action="{{ route('admin.login.store') }}">
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

            <button type="submit" class="auth-submit">管理者ログインする</button>
        </form>
    </section>
@endsection
