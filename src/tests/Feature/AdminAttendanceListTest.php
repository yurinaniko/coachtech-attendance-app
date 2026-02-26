<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_view_all_users_attendance_for_today()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $users = User::factory()->count(3)->create([
            'is_admin' => false,
        ]);

        foreach ($users as $user) {
            Attendance::factory()->create([
                'user_id'   => $user->id,
                'work_date' => today(),
            ]);
        }

        $response = $this->actingAs($admin)->get(
            route('admin.attendance.list')
        );

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        Carbon::setTestNow();
    }

    /** @test */
    public function today_date_is_displayed_on_admin_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.list'));

        $response->assertSee('2026年2月25日');

        Carbon::setTestNow();
    }

    /** @test */
    public function previous_day_button_displays_previous_attendance()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $user = User::factory()->create([
            'name' => 'テストユーザー'
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today()->subDay(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.list'));

        $response->assertSee('2026年2月25日');

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.list', [
                'date' => today()->subDay()->toDateString()
            ]));

        $response->assertSee('2026年2月24日');

        Carbon::setTestNow();
    }

    /** @test */
    public function next_day_button_displays_next_day_attendance()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 25));

        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $user = User::factory()->create([
            'name' => 'テストユーザー'
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
        ]);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today()->addDay(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.list'));

        $response->assertSee('2026年2月25日');

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.list', [
                'date' => today()->addDay()->toDateString()
            ]));

        $response->assertSee('2026年2月26日');

        Carbon::setTestNow();
    }
}