<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
    /** @test */
    public function date_is_displayed_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22));
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('2026年2月22日（日）');
    }

    /** @test */
    public function status_is_displayed_as_off_duty()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 8, 0));
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function status_is_displayed_as_working()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 10, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function status_is_displayed_as_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 12, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now(),
            'break_end_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function status_is_displayed_as_finished()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 18, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(9),
            'clock_out_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('退勤済');
    }

    /** @test */
    public function clock_in_button_is_displayed_and_clock_in_works()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('出勤', false);

        $this->actingAs($user)
            ->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
        ]);
    }

    /** @test */
    public function clock_in_button_is_not_displayed_after_clock_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 18, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(9),
            'clock_out_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertDontSee('出勤', false);
    }

    /** @test */
    public function clock_in_time_is_recorded_and_displayed_on_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/attendance/clock-in');

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertSee('09:00');
    }

    /** @test */
    public function break_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 12, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('休憩入');

        $this->actingAs($user)
            ->post('/attendance/break-start');

        $response = $this->actingAs($user)
        ->get('/attendance');

        $response->assertSee('休憩中');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_end_at' => null,
        ]);

    }

    /** @test */
    public function user_can_take_breaks_multiple_times_in_a_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 12, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-end');

        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-start');

        $this->assertEquals(
            2,
            AttendanceBreak::where('attendance_id', $attendance->id)->count()
        );
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 13, 0));

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function break_end_changes_status_to_working()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 12, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');

        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function user_can_end_break_multiple_times_in_a_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 12, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
        ]);

        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-end');

        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-end');

        $this->assertEquals(2, AttendanceBreak::count());

        $this->assertEquals(
            0,
            AttendanceBreak::whereNull('break_end_at')->count()
        );

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    /** @test */
    public function break_times_are_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 12, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-start');
        Carbon::setTestNow(now()->addMinutes(30));
        $this->actingAs($user)->post('/attendance/break-end');
        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('00:30');
    }

    /** @test */
    public function clock_out_button_works_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 18, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => today(),
            'clock_in_at'  => Carbon::create(2026, 2, 22, 9, 0),
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済');

        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'work_date' => today()->toDateString(),
        ]);

        $this->assertNotNull(
            Attendance::where('user_id', $user->id)
                ->whereDate('work_date', today())
                ->first()
                ->clock_out_at
        );
    }

    /** @test */
    public function clock_out_time_is_displayed_on_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 2, 22, 18, 0));
        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('18:00');
    }
}
