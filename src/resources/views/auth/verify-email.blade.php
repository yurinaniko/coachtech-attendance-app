@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper auth__wrapper--verify">
    <div class="auth auth--verify">
        @if (session('message'))
            <div class="auth__flash" id="flash-message">
                {{ session('message') }}
            </div>
            <script>
            setTimeout(() => {
                document.getElementById('flash-message')?.remove();
            }, 3000);
            </script>
        @endif
        <p class="auth__text">ご登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p class="auth__text">メール認証を完了してください。</p>
        <div class="auth__verify-actions">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" onclick="this.disabled=true; this.form.submit();" class="auth__link">
                    認証メールを再送する
                </button>
            </form>
            <a href="http://localhost:8025" target="_blank" class="auth__button">
                認証はこちらから
            </a>
        </div>
    </div>
</div>
@endsection