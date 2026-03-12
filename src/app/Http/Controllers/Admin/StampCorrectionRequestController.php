<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceBreak;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with([
            'user',
            'attendance',
            'attendance.breaks'
        ])
        ->where('status', $status)
        ->orderBy('created_at', 'desc')
        ->get();

        return view('admin.stamp_correction_request.list', compact('requests'));
    }

    public function edit($id)
    {
        $request = StampCorrectionRequest::with([
            'user',
            'attendance',
            'stampCorrectionBreaks.attendanceBreak',
        ])->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('request'));
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {
            $correctionRequest = StampCorrectionRequest::with([
                'attendance',
                'stampCorrectionBreaks.attendanceBreak'
            ])->findOrFail($id);
            $attendance = $correctionRequest->attendance;
            $attendance->update([
                'clock_in_at'  => $correctionRequest->requested_clock_in_at,
                'clock_out_at' => $correctionRequest->requested_clock_out_at,
                'note'         => $correctionRequest->requested_note,
            ]);

            if ($correctionRequest->stampCorrectionBreaks->isNotEmpty()) {
                AttendanceBreak::where('attendance_id', $attendance->id)->delete();
                foreach ($correctionRequest->stampCorrectionBreaks as $correctionBreak) {
                    if ($correctionBreak->break_start_at && $correctionBreak->break_end_at) {
                        AttendanceBreak::create([
                            'attendance_id'  => $attendance->id,
                            'break_start_at' => $correctionBreak->break_start_at,
                            'break_end_at'   => $correctionBreak->break_end_at,
                        ]);
                    }
                }
            }
            $correctionRequest->update(['status' => StampCorrectionRequest::STATUS_APPROVED,]);
        });
        return redirect()->route('admin.stamp_correction_request.edit', $id);
    }
}