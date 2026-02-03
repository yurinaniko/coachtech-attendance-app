@extends('layouts.app')
@section('body-class', 'body-auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/form.css') }}">
<link rel="stylesheet" href="{{ asset('css/auth.css') }}">
@endsection

@section('content')
<div class="auth__wrapper">
    <div class="auth auth--login">
        <h1 class="auth__title">管理者ログイン</h1>
            <form action="{{ route('admin.login') }}" method="POST" novalidate>
                @csrf
                @if ($errors->has('admin.login'))
                    <p class="form__error form__error--global">
                        {{ $errors->first('admin.login') }}
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
                <button class="auth__submit">管理者ログインする</button>
            </form>
    </div>
</div>
@endsection