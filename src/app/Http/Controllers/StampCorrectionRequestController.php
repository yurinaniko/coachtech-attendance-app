<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\StoreStampCorrectionRequest;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionBreak;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_requests.index', compact('requests'));
    }

    public function show($id)
    {
        $request = StampCorrectionRequest::with('user', 'attendance')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('stamp_correction_requests.show', compact('request'));
    }

    public function store(StoreStampCorrectionRequest $request)
    {
        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($attendance->work_date->isFuture()) {
            return back()->with('error', '※未来日のため修正できません。');
        }

        $data = $request->validated();

        $requestedClockIn = $data['clock_in_at']
            ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_in_at'])
            : null;

        $requestedClockOut = $data['clock_out_at']
            ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_out_at'])
            : null;

        $exists = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('error', '承認待ちの申請があります');
        }

        $correctionRequest = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => auth()->id(),
            'status'        => 'pending',

            'requested_clock_in_at'  => $requestedClockIn,
            'requested_clock_out_at' => $requestedClockOut,
            'requested_note'         => $data['note'],
        ]);

        foreach ($data['breaks'] ?? [] as $break) {
            $attendanceBreakId = $break['attendance_break_id'] ?? null;
            $breakStartAt      = $break['break_start_at'] ?? null;
            $breakEndAt        = $break['break_end_at'] ?? null;

            if (!$attendanceBreakId && !$breakStartAt && !$breakEndAt) {
                continue;
            }

            $breakStart = $breakStartAt
            ? $attendance->work_date->copy()->setTimeFromTimeString($breakStartAt)
            : null;

            $breakEnd = $breakEndAt
            ? $attendance->work_date->copy()->setTimeFromTimeString($breakEndAt)
            : null;

            StampCorrectionBreak::updateOrCreate(
                [
                    'stamp_correction_request_id' => $correctionRequest->id,
                    'attendance_break_id'         => $attendanceBreakId,
                ],
                [
                    'break_start_at' => $breakStart,
                    'break_end_at'   => $breakEnd,
                ]
            );
        }

        return redirect()
            ->route('attendance.detailByDate', [
            'date' => $attendance->work_date->format('Y-m-d')
        ]);
    }

    public function update(StoreStampCorrectionRequest $request, $id)
    {
        $correctionRequest = StampCorrectionRequest::where('user_id', auth()->id())
            ->findOrFail($id);
        $attendance = $correctionRequest->attendance;

        DB::transaction(function () use ($request, $attendance) {

            $data = $request->validated();

            $clockIn = $data['clock_in_at']
                ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_in_at'])
                : null;

            $clockOut = $data['clock_out_at']
                ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_out_at'])
                : null;

            $correctionRequest->update([
                'requested_clock_in_at'  => $clockIn,
                'requested_clock_out_at' => $clockOut,
                'requested_note'         => $data['note'],
            ]);

            foreach ($data['breaks'] ?? [] as $breakData) {
                $attendanceBreak = AttendanceBreak::find($breakData['attendance_break_id']);
                if ($attendanceBreak) {
                    $breakStart = $breakData['break_start_at']
                        ? $attendance->work_date->copy()->setTimeFromTimeString($breakData['break_start_at'])
                        : null;
                    $breakEnd = $breakData['break_end_at']
                        ? $attendance->work_date->copy()->setTimeFromTimeString($breakData['break_end_at'])
                        : null;

                    StampCorrectionBreak::updateOrCreate(
                        [
                            'stamp_correction_request_id' => $correctionRequest->id,
                            'attendance_break_id'         => $attendanceBreak->id,
                        ],
                        [
                            'break_start_at' => $breakStart,
                            'break_end_at'   => $breakEnd,
                        ]
                    );
                }
            }
    });

    return redirect()
        ->route('attendance.detailByDate', [
            'date' => $attendance->work_date->format('Y-m-d')
        ]);
    }
}
