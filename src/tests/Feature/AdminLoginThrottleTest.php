<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 管理者ログインの総当たり対策。
 *
 * Fortify は config/fortify.php の limiters.login が設定されていると
 * 内部のロックアウト（EnsureLoginIsNotThrottled）を自ら外し、ルート側の
 * throttle に委譲する。そのため標準ルートを使わず自作すると、
 * 防御が二重になるどころかゼロになる。
 */
class AdminLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者ログインは試行回数の上限を超えると弾かれる()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // 上限（10回/分）までは通常どおり認証失敗として扱われる
        for ($i = 0; $i < 10; $i++) {
            $this->post('/admin/login', [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ])->assertStatus(302);
        }

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    /** @test */
    public function ログイン済みの利用者は管理者ログイン画面へ入れない()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin/login')
            ->assertRedirect();
    }
}
