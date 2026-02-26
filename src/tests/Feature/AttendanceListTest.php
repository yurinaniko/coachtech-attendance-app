<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
     /** @test */
    public function user_sees_only_their_attendances()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Attendance::factory()->count(3)->sequence(
            [
                'work_date' => now()->subDays(1)->toDateString(),
                'clock_in_at' => '07:11:00',
                'clock_out_at' => '19:11:00',
            ],
            [
                'work_date' => now()->subDays(2)->toDateString(),
                'clock_in_at' => '07:12:00',
                'clock_out_at' => '19:12:00',
            ],
            [
                'work_date' => now()->subDays(3)->toDateString(),
                'clock_in_at' => '07:13:00',
                'clock_out_at' => '19:13:00',
            ],
            )->create(['user_id' => $user->id]);

        Attendance::factory()->count(2)->sequence(
            [
                'work_date' => now()->subDays(10)->toDateString(),
                'clock_in_at' => '06:01:00',
                'clock_out_at' => '20:01:00',
            ],
            [
                'work_date' => now()->subDays(11)->toDateString(),
                'clock_in_at' => '06:02:00',
                'clock_out_at' => '20:02:00',
            ],
        )->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSee('07:11')->assertSee('19:11');

        $response->assertDontSee('06:01')->assertDontSee('20:01');
    }

    /** @test */
    public function current_month_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $month = now()->format('Y/m');

        $response->assertSee($month);
        $response->assertStatus(200);
    }
    /** @test */
    public function previous_month_is_displayed()
    {
        $user = User::factory()->create();

        $previousMonth = now()->subMonth()->format('Y-m');

        $response = $this->actingAs($user)
            ->get(route('attendance.list', [
                'month' => $previousMonth
            ]));

        $expectedDisplay = now()->subMonth()->format('Y/m');

        $response->assertSee($expectedDisplay);
    }
    /** @test */
    public function next_month_is_displayed()
    {
        $user = User::factory()->create();

        $nextMonth = now()->addMonth()->format('Y-m');
        $response = $this->actingAs($user)
            ->get(route('attendance.list', [
                'month' => $nextMonth
            ]));

        $expectedDisplay = now()->addMonth()->format('Y/m');

        $response->assertSee($expectedDisplay);
    }
    /** @test */
    public function attendance_detail_page_is_accessible()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detailByDate', [
                'date' => $attendance->work_date->format('Y-m-d')
            ]));

        $response->assertStatus(200);
    }
}
