<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;

class AdminStampCorrectionApproveTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function pending_requests_are_displayed_on_admin_request_list()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $pendingRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_note' => 'pending申請',
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'requested_note' => 'approved申請',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_requests.index', [
                'status' => 'pending',
            ]));

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($pendingRequest->requested_note);

        $response->assertSee('pending申請');
        $response->assertDontSee('approved申請');

    }

    /** @test */
    public function approved_requests_are_displayed_on_admin_request_list()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        $approvedRequest = StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'requested_note' => 'approved申請',
        ]);

        StampCorrectionRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'requested_note' => 'pending申請',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_requests.index', [
                'status' => 'approved',
            ]));

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee($approvedRequest->requested_note);

        $response->assertSee('approved申請');
        $response->assertDontSee('pending申請');
    }

    /** @test */
    public function stamp_correction_request_detail_is_displayed_correctly()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 2, 1),
            'clock_in_at' => Carbon::create(2026, 2, 1, 9, 0),
            'clock_out_at' => Carbon::create(2026, 2, 1, 18, 0),
            'note' => '元の備考',
        ]);

        $request = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_clock_in_at' => '10:00',
            'requested_clock_out_at' => '19:00',
            'requested_note' => '修正理由テスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_requests.edit', $request->id));

        $response->assertStatus(200);

        $response->assertSee($user->name);

        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('修正理由テスト');
    }

    /** @test */
    public function admin_can_approve_stamp_correction_request()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 2, 1),
            'clock_in_at' => Carbon::create(2026, 2, 1, 9, 0),
            'clock_out_at' => Carbon::create(2026, 2, 1, 18, 0),
            'note' => '元の備考',
        ]);

        $request = StampCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_clock_in_at' => '10:00',
            'requested_clock_out_at' => '19:00',
            'requested_note' => '承認テスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.stamp_correction_requests.approve', $request->id));

        $response->assertStatus(302); // リダイレクト確認

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'note' => '承認テスト',
        ]);

        $updatedAttendance = Attendance::find($attendance->id);

        $this->assertEquals('10:00', $updatedAttendance->clock_in_at->format('H:i'));
        $this->assertEquals('19:00', $updatedAttendance->clock_out_at->format('H:i'));
    }
}