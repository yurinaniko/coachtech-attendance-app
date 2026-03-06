<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function attendance_detail_displays_logged_in_user_name()
    {
        $user = User::factory()->create([
            'name' => 'テストユーザー'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', [
                'date' => $attendance->work_date->format('Y-m-d')
            ]));

        $response->assertSee('テストユーザー');
    }

    /** @test */
    public function attendance_detail_displays_correct_date()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-01',
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', [
                'date' => '2026-02-01'
            ]));

        $response->assertSee('2026年');
        $response->assertSee('2月1日');
    }

    /** @test */
    public function clock_in_and_clock_out_times_are_displayed_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => '2026-02-01',
            'clock_in_at'  => Carbon::parse('2026-02-01 09:00:00'),
            'clock_out_at' => Carbon::parse('2026-02-01 18:00:00'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', [
                'date' => $attendance->work_date->format('Y-m-d')
            ]));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function break_times_are_displayed_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2026-02-01',
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::parse('2026-02-01 12:00:00'),
            'break_end_at'   => Carbon::parse('2026-02-01 13:00:00'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', [
                'date' => $attendance->work_date->format('Y-m-d')
            ]));

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
