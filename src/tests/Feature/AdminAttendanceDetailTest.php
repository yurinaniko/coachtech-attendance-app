<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function selected_attendance_is_displayed_on_detail_screen()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $user = User::factory()->create([
            'name' => '表示確認ユーザー'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.detail', $attendance->id)
        );

        $response->assertStatus(200);

        $response->assertSee('表示確認ユーザー');

        Carbon::setTestNow();
    }

    /** @test */
    public function admin_cannot_update_when_clock_in_is_later_than_clock_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in_at'  => '19:00',
                'clock_out_at' => '09:00',
                'note' => 'テスト'
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in_at'
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function break_start_time_cannot_be_later_than_clock_out_time()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1, 9, 0));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => today(),
            'clock_in_at'  => today()->copy()->setTime(9, 0),
            'clock_out_at' => today()->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in_at'  => '09:00',
                'clock_out_at' => '18:00',

                'breaks' => [
                    [
                        'break_start_at' => '19:00', // ←退勤より後
                        'break_end_at'   => '20:00',
                    ]
                ],

                'note' => '休憩エラーテスト'
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.break_start_at'
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function break_end_time_cannot_be_later_than_clock_out_time()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 9, 0));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $attendance = Attendance::factory()->create([
            'work_date'    => today(),
            'clock_in_at'  => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in_at'  => '09:00',
                'clock_out_at' => '18:00',

                'breaks' => [
                    [
                        'break_start_at' => '17:00',
                        'break_end_at'   => '19:00',
                    ]
                ],

                'note' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.break_end_at'
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function note_is_required_on_admin_update()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25, 9, 0));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $attendance = Attendance::factory()->create([
            'work_date'    => today(),
            'clock_in_at'  => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in_at'  => '09:00',
                'clock_out_at' => '18:00',

                'note' => '', // ← NG
            ]
        );

        $response->assertSessionHasErrors([
            'note'
        ]);

        Carbon::setTestNow();
    }
}
