<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrectionBreak;
use App\Http\Requests\StoreStampCorrectionRequest;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with('user')
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
        $data = $request->validated();

        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->first();

        $requestedClockIn = $data['clock_in_at']
            ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_in_at'])
            : null;

        $requestedClockOut = $data['clock_out_at']
            ? $attendance->work_date->copy()->setTimeFromTimeString($data['clock_out_at'])
            : null;

        $correctionRequest = StampCorrectionRequest::updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'user_id'       => auth()->id(),
                'status'        => 'pending',
            ],
            [
                'requested_clock_in_at'  => $requestedClockIn,
                'requested_clock_out_at' => $requestedClockOut,
                'requested_note'         => $data['note'],
            ]
        );

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

            // 既存の休憩がない場合は attendance_breaks に作成
            if (!$attendanceBreakId) {
                $attendanceBreak = $attendance->breaks()->create([
                'break_start_at' => $breakStart,
                'break_end_at'   => $breakEnd,
            ]);

            $attendanceBreakId = $attendanceBreak->id;
            }

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
            ->route('attendance.detail.byDate', [
            'date' => $attendance->work_date->format('Y-m-d')
        ])
            ->with('success', '修正申請を送信しました');
    }
}
