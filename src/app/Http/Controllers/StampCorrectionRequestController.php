<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\StampCorrectionBreak;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = StampCorrectionRequest::with('user')
            ->where('user_id', auth()->id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $requests = $query->latest()->get();

        return view('stamp_correction_requests.index', compact('requests'));
    }

    public function show($id)
    {
        $request = StampCorrectionRequest::with('user', 'attendance')
            ->findOrFail($id);

        return view('stamp_correction_requests.show', compact('request'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'attendance_id' => ['required', 'exists:attendances,id'],
            'clock_in_at'   => ['nullable'],
            'clock_out_at'  => ['nullable'],
            'note'          => ['required', 'string'],
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        $existsPending = StampCorrectionRequest::where('user_id', auth()->id())
            ->where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return back()->withErrors([
                'message' => 'この日の修正申請はすでに承認待ちです。'
            ]);
        }

        $attendance = Attendance::where('id', $request->attendance_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $requestedClockIn = $request->clock_in_at ? $attendance->work_date
            ->copy()
            ->setTimeFromTimeString($request->clock_in_at): null;

        $requestedClockOut = $request->clock_out_at ? $attendance->work_date
            ->copy()
            ->setTimeFromTimeString($request->clock_out_at): null;
        // 親：打刻修正申請
        $correctionRequest = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => auth()->id(),
            'requested_clock_in_at'  => $requestedClockIn,
            'requested_clock_out_at' => $requestedClockOut,
            'requested_note'         => $request->note,
            'status' => 'pending',
        ]);

        foreach ($request->breaks as $break) {

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

            StampCorrectionBreak::create([
                'stamp_correction_request_id' => $correctionRequest->id,
                'attendance_break_id' => $attendanceBreakId,
                'break_start_at' => $breakStart,
                'break_end_at'   => $breakEnd,
            ]);
        }

        return redirect()
            ->route('attendance.show', $attendance->id)
            ->with('success', '修正申請を送信しました');
    }
}