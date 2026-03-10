@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper">
    <div class="auth auth--verify">
        @if (session('message'))
            <div class="auth__flash js-flash-message">
                {{ session('message') }}
            </div>
        @endif
        <p class="auth__text">ご登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p class="auth__text">メール認証を完了してください。</p>
        <div class="auth__verify-actions">
            <form class="auth__form" method="POST" action="{{ route('verification.send') }}">
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
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const flash = document.querySelector('.js-flash-message');
    if (flash) {
        setTimeout(() => {
            flash.remove();
        }, 3000);
    }
});
</script>
@endpush
@endsection