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
    public function clock_in_button_is_displayed_before_work()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get('/attendance');
        $response->assertSee('出勤', false);
        $response->assertDontSee('退勤', false);
        $response->assertDontSee('休憩', false);
    }

    /** @test */
    public function buttons_are_displayed_while_working()
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

        $response->assertSee('休憩', false);
        $response->assertSee('退勤', false);
        $response->assertDontSee('>出勤<', false);
    }

    /** @test */
    public function break_return_button_is_displayed()
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

        $response->assertSee('休憩戻', false);
        $response->assertDontSee('退勤', false);
    }

    /** @test */
    public function no_buttons_are_displayed_after_work()
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

        $response->assertDontSee('>退勤<', false);
        $response->assertDontSee('>休憩<', false);
        $response->assertDontSee('>出勤<', false);
    }

    /** @test */
    public function user_can_clock_in()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_out_at' => null,
        ]);
    }
    /** @test */
    public function user_can_clock_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 18, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now()->subHours(9),
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
        ]);

        $this->assertNotNull(
            Attendance::where('user_id', $user->id)->first()->clock_out_at
        );
    }

    /** @test */
    public function user_can_start_break()
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
            ->post('/attendance/break-start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_end_at' => null,
        ]);
    }

    /** @test */
    public function user_can_end_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 13, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now()->subHour(),
            'break_end_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/break-end');

        $response->assertRedirect('/attendance');

        $this->assertNotNull(
            AttendanceBreak::where('attendance_id', $attendance->id)
                ->latest()
                ->first()
                ->break_end_at
        );
    }

    /** @test */
    public function user_cannot_clock_in_twice()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));

        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/clock-in');

        $response->assertSessionHasErrors();
    }
}
