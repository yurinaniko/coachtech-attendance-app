@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper">
    <div class="auth auth--login">
        <h1 class="auth__title">
            @if(request()->routeIs('admin.login'))
                管理者ログイン
            @else
                ログイン
            @endif
        </h1>
        <form class="auth__form" action="{{ request()->routeIs('admin.login') ? route('admin.login.post') : route('login') }}" method="POST" novalidate>
            @csrf
            @php
                $errorKey = request()->routeIs('admin.login') ? 'admin.login' : 'login';
            @endphp
            @if ($errors->has($errorKey))
                <p class="auth__error auth__error--global">
                    {{ $errors->first($errorKey) }}
                </p>
            @endif
            <div class="auth__group">
                <label class="auth__label">メールアドレス</label>
                <input type="email" class="auth__input" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="auth__error">{{ $message }}</p>
                @enderror
            </div>
            <div class="auth__group">
                <label class="auth__label">パスワード</label>
                <input type="password" class="auth__input" name="password">
                @error('password')
                    <p class="auth__error">{{ $message }}</p>
                @enderror
            </div>
            <button class="auth__submit">
                @if(request()->routeIs('admin.login'))
                    管理者ログインする
                @else
                    ログインする
                @endif
            </button>
        </form>
        @if(!request()->routeIs('admin.login'))
            <a href="{{ route('register') }}" class="auth__link">
                会員登録はこちら
            </a>
        @endif
    </div>
</div>
@endsection