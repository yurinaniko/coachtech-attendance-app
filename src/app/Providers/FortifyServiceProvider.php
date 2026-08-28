<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\LoginResponse;
use App\Actions\Fortify\LoginUser;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::authenticateUsing(function ($request) {
            return app(LoginUser::class)($request);
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        $this->app->singleton(
            LoginResponseContract::class,
            LoginResponse::class
        );

        RateLimiter::for('login', function (Request $request) {
        $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        // config/fortify.php の limiters で 'two-factor' を参照しているが未定義だと
        // 2FAチャレンジ経路で例外になり、総当たり防御も効かない。IP単位で制限する。
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id') . $request->ip());
        });

        // 登録・パスワードリセット等のFortifyルート全体の安全網（大量登録・メール爆撃の抑止）。
        RateLimiter::for('fortify', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });
    }
}
