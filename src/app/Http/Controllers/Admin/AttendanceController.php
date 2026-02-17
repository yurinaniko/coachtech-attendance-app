<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Http\Requests\StoreStampCorrectionRequest;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = Carbon::parse(
            $request->query('date', now()->toDateString())
        );

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', compact('date', 'attendances'));
    }

    public function monthly(Request $request, User $user)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('work_date', [$start, $end])
        ->orderBy('work_date')
        ->get();

        return view(
        'admin.staff.attendance.index',
        compact('user', 'attendances', 'month')
        );
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        $breaks = $attendance->breaks()
            ->orderBy('break_start_at')
            ->get();

        $oldBreaks = old('breaks');

        $displayCount = min($breaks->count() + 1, 5);

        return view('admin.attendance.detail', compact(
            'attendance',
            'breaks',
            'displayCount'
        ));
    }

    public function update(StoreStampCorrectionRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {

        $data = $request->validated();
        $attendance->update([
            'clock_in_at'  => $data['clock_in_at'],
            'clock_out_at' => $data['clock_out_at'],
            'note'         => $data['note'] ?? null,
            'status' => 'approved',
        ]);

            // 休憩時間の更新と作成
            foreach ($data['breaks'] ?? [] as $breakData) {

                if (empty($breakData['break_start_at']) &&
                empty($breakData['break_end_at'])) {
                    continue;
                }

                if (!empty($breakData['attendance_break_id'])) {
                    // 更新
                    AttendanceBreak::where('id', $breakData['attendance_break_id'])
                        ->update([
                            'break_start_at' => $breakData['break_start_at'],
                            'break_end_at'   => $breakData['break_end_at'],
                        ]);

                } else {
                    // 新規作成
                    $attendance->breaks()->create([
                        'break_start_at' => $breakData['break_start_at'],
                        'break_end_at'   => $breakData['break_end_at'],
                    ]);
                }
            }

            StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
            ]);
        });
                return redirect()
                    ->route('admin.attendance.detail', $attendance->id)
                    ->with('success', '勤怠を修正しました');
    }

}
