@extends('layouts.app')

@section('title', 'メール認証')
@section('main_class', 'verify-email-page')

@section('content')
    <section class="verify-email-card">
        <p class="verify-email-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <a href="http://localhost:8025" target="_blank" rel="noopener" class="verify-email-button">認証はこちらから</a>

        <form method="POST" action="{{ route('verification.send') }}" class="verify-email-resend-form">
            @csrf
            <button type="submit" class="verify-email-resend-link">認証メールを再送する</button>
        </form>
    </section>
@endsection
