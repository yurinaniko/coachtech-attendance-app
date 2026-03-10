<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;
use App\Http\Requests\Admin\AttendanceUpdateRequest;

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

    public function monthly(Request $request, User $user, $month)
    {
        $period = Carbon::parse($month);
        $month = $request->query('month');
        $start = $period->copy()->startOfMonth();
        $end   = $period->copy()->endOfMonth();

        $dates = [];
        $current = $start->copy();

        while ($current <= $end) {
            $attendance = Attendance::firstOrCreate([
                'user_id'   => $user->id,
                'work_date' => $current->toDateString(),
            ]);

            $dates[] = $attendance;
            $current->addDay();
        }

        $attendances = collect($dates);
        return view('admin.attendance.monthly', compact(
            'user',
            'attendances',
            'period'
        ));
    }

    public function detail($id)
    {
        $attendance = Attendance::with([
            'user',
            'breaks' => function ($query) {
                $query->orderBy('break_start_at');
            }
        ])->findOrFail($id);

        $breaks = $attendance->breaks;
        $isFuture = $attendance->work_date->isFuture();

        $clockIn  = $attendance->clock_in_at;
        $clockOut = $attendance->clock_out_at;

        $oldBreaks = old('breaks');

        $displayCount = max(1, min($breaks->count() + 1, 5));

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $id)
        ->where('status', 'pending')
        ->first();

        $isStatic = $isFuture || $pendingRequest;
        return view('admin.attendance.detail', compact(
            'attendance',
            'clockIn',
            'clockOut',
            'breaks',
            'displayCount',
            'pendingRequest',
            'isFuture',
            'isStatic'
        ));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        if ($attendance->work_date->isFuture()) {
            abort(403, '未来日は編集できません');
        }

        DB::transaction(function () use ($request, $attendance) {

            $data = $request->validated();

            $clockIn = $data['clock_in_at']
                ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_in_at'])
                : null;

            $clockOut = $data['clock_out_at']
                ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_out_at'])
                : null;

            $attendance->update([
                'clock_in_at'  => $clockIn,
                'clock_out_at' => $clockOut,
                'note'         => $data['note'] ?? null,
            ]);

            foreach ($data['breaks'] ?? [] as $breakData) {

                if (empty($breakData['break_start_at']) &&
                empty($breakData['break_end_at'])) {
                    continue;
                }

                $breakStart = $breakData['break_start_at']
                    ? $attendance->work_date->copy()->setTimeFromTimeString($breakData['break_start_at'])
                    : null;

                $breakEnd = $breakData['break_end_at']
                    ? $attendance->work_date->copy()->setTimeFromTimeString($breakData['break_end_at'])
                    : null;

                if (!empty($breakData['attendance_break_id'])) {

                    AttendanceBreak::where('id', $breakData['attendance_break_id'])
                    ->update([
                        'break_start_at' => $breakStart,
                        'break_end_at'   => $breakEnd,
                    ]);

                } else {

                    $attendance->breaks()->create([
                        'break_start_at' => $breakStart,
                        'break_end_at'   => $breakEnd,
                    ]);
                }
            }

            $requestRecord = StampCorrectionRequest::Create(
                [
                    'attendance_id' => $attendance->id,
                    'type'          => StampCorrectionRequest::TYPE_ADMIN,
                ],
                [
                    'user_id'                => $attendance->user_id,
                    'requested_clock_in_at'  => $clockIn,
                    'requested_clock_out_at' => $clockOut,
                    'requested_note'         => $data['note'] ?? null,
                    'status'                 => StampCorrectionRequest::STATUS_APPROVED,
                ]
            );

            $requestRecord->stampCorrectionBreaks()->delete();

            foreach ($data['breaks'] ?? [] as $breakData) {

                if (empty($breakData['break_start_at']) && empty($breakData['break_end_at'])) {
                    continue;
                }

                $breakStart = $attendance->work_date->copy()
                    ->setTimeFromTimeString($breakData['break_start_at']);

                $breakEnd = $attendance->work_date->copy()
                    ->setTimeFromTimeString($breakData['break_end_at']);

                $requestRecord->stampCorrectionBreaks()->create([
                    'attendance_break_id' => $breakData['attendance_break_id'] ?? null,
                    'break_start_at' => $breakStart,
                    'break_end_at'   => $breakEnd,
                ]);
            }
        });

        return redirect()
            ->route('admin.attendance.detail', $attendance->id);
    }
}
