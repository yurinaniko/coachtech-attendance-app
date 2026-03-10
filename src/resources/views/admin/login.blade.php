@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper">
    <div class="auth auth--login">
        <h1 class="auth__title">管理者ログイン</h1>
        <form class="auth__form" action="{{ route('admin.login') }}" method="POST" novalidate>
            @csrf
            @if ($errors->has('admin.login'))
                <p class="auth__error auth__error--global">
                    {{ $errors->first('admin.login') }}
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
            <button class="auth__submit">管理者ログインする</button>
        </form>
    </div>
</div>
@endsection