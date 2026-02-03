@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper">
    <div class="auth auth--login">
        <h1 class="auth__title">ログイン</h1>
            <form action="{{ route('login') }}" method="POST" novalidate>
                @csrf
                @if ($errors->has('login'))
                    <p class="form__error form__error--global">
                        {{ $errors->first('login') }}
                    </p>
                @endif
                <div class="form__group">
                    <label class="form__label">メールアドレス</label>
                    <input type="email" class="form__input" name="email" value="{{ old('email') }}">
                    @error('email')
                        <p class="form__error">{{ $message }}</P>
                    @enderror
                </div>
                <div class="form__group">
                    <label class="form__label">パスワード</label>
                    <input type="password" class="form__input" name="password">
                    @error('password')
                        <p class="form__error">{{ $message }}</P>
                    @enderror
                </div>
                <button class="auth__submit">ログインする</button>
            </form>
            <a href="{{ route('register.form') }}" class="auth__link">会員登録はこちら</a>
    </div>
</div>
@endsection