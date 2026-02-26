<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;


class VerifyEmailTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function verification_email_is_sent_after_register()
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test */
    public function verification_notice_page_shows_link_to_email_verification_site()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get(route('verification.notice'));

        $response->assertStatus(200);

        $response->assertSee('認証はこちらから');

        $response->assertSee('href="http://localhost:8025"', false);
    }


    /** @test */
    public function verified_user_is_redirected_to_attendance_page()
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        $notification = Notification::sent($user, VerifyEmail::class)->first();

        $response = $this->get($notification->toMail($user)->actionUrl);

        $response->assertRedirect(route('attendance.index'));
    }
}
