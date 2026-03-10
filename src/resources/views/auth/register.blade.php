@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper">
    <div class="auth auth--register">
        <h1 class="auth__title">会員登録</h1>
        <form class="auth__form" action="{{ route('register') }}" method="POST" novalidate>
            @csrf
            <div class="auth__group">
                <label class="auth__label">ユーザー名</label>
                <input type="text" name="name" class="auth__input" value="{{ old('name') }}">
                @error('name')
                    <p class="auth__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="auth__group">
                <label class="auth__label">メールアドレス</label>
                <input type="email" name="email" class="auth__input" value="{{ old('email') }}">
                @error('email')
                    <p class="auth__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="auth__group">
                <label class="auth__label">パスワード</label>
                <input type="password" name="password" class="auth__input">
                @error('password')
                    <p class="auth__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="auth__group">
                <label class="auth__label">確認用パスワード</label>
                <input type="password" name="password_confirmation" class="auth__input">
                @error('password_confirmation')
                    <p class="auth__error">{{ $message }}</p>
                @enderror
            </div>
            <button class="auth__submit">登録する</button>
        </form>
        <a href="{{ route('login') }}" class="auth__link">ログインはこちら</a>
    </div>
</div>
@endsection
