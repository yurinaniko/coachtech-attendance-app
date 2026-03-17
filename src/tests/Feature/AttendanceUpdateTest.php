<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function clock_in_time_cannot_be_later_than_clock_out_time()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($user)->post(
            route('stamp_correction_request.store'),
            [
                'attendance_id' => $attendance->id,
                'clock_in_at'  => '18:00',
                'clock_out_at' => '09:00',
                'note' => 'テスト'
            ]
        );

        $response->assertSessionHasErrors([
            'clock_in_at'
        ]);
    }

    /** @test */
    public function break_start_time_cannot_be_later_than_clock_out_time()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1, 9, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => today(),
            'clock_in_at'  => now(),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->post(
            route('stamp_correction_request.store'),
            [
                'attendance_id' => $attendance->id,
                'clock_in_at'  => '09:00',
                'clock_out_at' => '18:00',
                'breaks' => [
                    [
                        'break_start_at' => '19:00',
                        'break_end_at'   => '20:00',
                    ]
                ],

                'note' => 'テスト'
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
        Carbon::setTestNow(Carbon::create(2026, 2, 1, 9, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => today(),
            'clock_in_at'  => now(),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->post(
            route('stamp_correction_request.store'),
            [
                'attendance_id' => $attendance->id,
                'clock_in_at'  => '09:00',
                'clock_out_at' => '18:00',

                'breaks' => [
                    [
                        'break_start_at' => '17:00',
                        'break_end_at'   => '19:00',
                    ]
                ],

                'note' => 'テスト'
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.break_end_at'
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function note_is_required()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 1, 9, 0));

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'      => $user->id,
            'work_date'    => today(),
            'clock_in_at'  => now(),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)->post(
            route('stamp_correction_request.store'),
            [
                'attendance_id' => $attendance->id,
                'clock_in_at'  => '09:00',
                'clock_out_at' => '18:00',

                'note' => ''
            ]
        );

        $response->assertSessionHasErrors([
            'note'
        ]);

        Carbon::setTestNow();
    }

    /** @test */
    public function correction_request_is_visible_on_admin_screens()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));

        $user = User::factory()->create([
            'is_admin' => false,
            'name' => '一般ユーザー'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(
            route('stamp_correction_request.store'),
            [
                'attendance_id' => $attendance->id,
                'clock_in_at'   => '05:00',
                'clock_out_at'  => '08:00',
                'note'          => '修正申請テスト'
            ]
        );
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id'  => $attendance->id,
            'requested_note' => '修正申請テスト',
            'status'         => 'pending',
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
            'name' => '管理者'
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.index'));

        $response->assertSee('修正申請テスト');

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.list'));

        $response->assertSee('一般ユーザー');

        Carbon::setTestNow();
    }

    /** @test */
    public function pending_request_becomes_visible_after_approval()
    {
        Carbon::setTestNow(Carbon::create(2026, 2, 22, 9, 0));

        $user = User::factory()->create([
            'is_admin' => false,
            'name' => '一般ユーザー'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in_at' => now(),
        ]);

        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_clock_in_at'  => '10:00',
            'requested_clock_out_at' => '19:00',
            'requested_note' => '承認テスト',
            'status' => 'pending'
        ]);

        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $this->actingAs($admin)->post(
            route('admin.stamp_correction_request.approve', $request->id)
        );

        $response = $this->actingAs($admin)->get(
            route('admin.stamp_correction_request.index', [
            'status' => 'approved'
            ])
        );

        $response->assertSee('承認テスト');

        Carbon::setTestNow();
    }

    /** @test */
    public function approved_requests_are_visible_on_approved_tab()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_note' => '承認テスト',
            'status' => 'approved',
        ]);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get(
            route('admin.stamp_correction_request.index', [
                'status' => 'approved'
            ])
        );

        $response->assertSee('承認テスト');
    }

    /** @test */
    public function user_can_navigate_to_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('詳細');

        $detailResponse = $this->actingAs($user)->get(
            route('attendance.detail', $attendance->work_date)
        );

        $detailResponse->assertStatus(200);
    }

    /** @test */
    public function pending_requests_are_displayed_in_pending_tab()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_note' => 'テスト申請',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('テスト申請');
    }

    /** @test */
    public function approved_requests_are_displayed_in_approved_tab()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'requested_note' => '承認済み申請',
            'status' => 'approved'
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み申請');
    }

    /** @test */
    public function user_can_access_request_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id
        ]);

        $request = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending'
        ]);

        $admin = User::factory()->create([
            'is_admin' => true
        ]);

        $response = $this->actingAs($admin)
            ->get("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
    }
}