<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Tests\TestCase;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function admin_can_view_all_users_name_and_email()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $users = User::factory()->count(3)->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function staff_attendance_information_is_displayed_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->create([
            'is_admin' => false,
            'name' => 'テストユーザー',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => today()->copy()->setTime(9, 0),
            'clock_out_at' => today()->copy()->setTime(18, 0),
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => today()->copy()->setTime(12, 0),
            'break_end_at' => today()->copy()->setTime(13, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.index', [
                'user' => $user->id,
                'month' => today()->format('Y-m'),
            ]));

        $response->assertStatus(200);

        $response->assertSee(today()->format('m/d'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('01:00');

        $response->assertSee('08:00');
    }

    /** @test */
    public function previous_month_attendance_is_displayed_when_navigating_to_previous_month()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['is_admin' => false]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 1, 10),
            'clock_in_at'  => Carbon::create(2026, 1, 10, 9, 0),
            'clock_out_at' => Carbon::create(2026, 1, 10, 18, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.index', [
                'user'  => $user->id,
                'month' => '2026-01',
            ]));

        $response->assertStatus(200);

        $response->assertSee('2026/01');
        $response->assertSee('01/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function next_month_attendance_is_displayed_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create(['is_admin' => true]);
        $user  = User::factory()->create(['is_admin' => false]);

        $workDate = Carbon::create(2026, 3, 10);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in_at' => $workDate->copy()->setTime(9, 0),
            'clock_out_at' => $workDate->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.index', [
                'user'  => $user->id,
                'month' => '2026-03',
            ]));

        $response->assertStatus(200);

        $response->assertSee('2026/03');
        $response->assertSee('03/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function admin_can_navigate_to_attendance_detail()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $workDate = Carbon::create(2026, 2, 10);

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => $workDate,
            'clock_in_at'  => $workDate->copy()->setTime(9, 0),
            'clock_out_at' => $workDate->copy()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.attendance.index', [
                'user'  => $user->id,
                'month' => '2026-02',
            ]));

        $response->assertStatus(200);

        $response->assertSee('詳細');

        $response->assertSee(route('admin.attendance.detail', $attendance->id));
    }
}
