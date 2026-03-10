<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceBreak;
use App\Http\Requests\Admin\AttendanceUpdateRequest;

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

    public function approve(AttendanceUpdateRequest $request, $id)
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
                // 既存休憩削除
                AttendanceBreak::where('attendance_id', $attendance->id)->delete();
                // 休憩再作成
                foreach ($correctionRequest->stampCorrectionBreaks as $correctionBreak) {
                    AttendanceBreak::create([
                        'attendance_id'  => $attendance->id,
                        'break_start_at' => $correctionBreak->break_start_at,
                        'break_end_at'   => $correctionBreak->break_end_at,
                    ]);
                }

            $correctionRequest->update(['status' => StampCorrectionRequest::STATUS_APPROVED,]);
        });

        return redirect()->route('admin.stamp_correction_request.edit', $id);
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $id) {

            $correctionRequest = StampCorrectionRequest::findOrFail($id);

            $attendance = $correctionRequest->attendance;

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
                'status' => 'approved',
            ]);

            $attendance->update([
                'clock_in_at'  => $clockIn,
                'clock_out_at' => $clockOut,
                'note'         => $data['note'],
            ]);

            $attendance->breaks()->delete();

            foreach ($data['breaks'] ?? [] as $break) {

                if (empty($break['break_start_at']) && empty($break['break_end_at'])) {
                    continue;
                }

                $attendance->breaks()->create([
                    'break_start_at' => $break['break_start_at'],
                    'break_end_at'   => $break['break_end_at'],
                ]);
            }
        });
        return redirect()->route('admin.stamp_correction_request.index');
    }
}