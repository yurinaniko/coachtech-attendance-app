<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        auth()->login($user);
        $user->sendEmailVerificationNotification();
        return redirect()->route('verification.notice');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
        return back()->withErrors([
            'login' => 'ログイン情報が登録されていません',
        ])->withInput();
        }

        $request->session()->regenerate();
        return redirect()->route('attendance.index');
    }

    public function showVerifyEmail()
    {
        return view('auth.verify-email');
    }
}
