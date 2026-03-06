<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use App\Models\AttendanceBreak;

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

    public function store(Request $request)
    {
        $attendance = Attendance::findOrFail($request->attendance_id);

        $clockIn = $request->clock_in_at
            ? $attendance->work_date->copy()->setTimeFromTimeString($request->clock_in_at)
            : null;

        $clockOut = $request->clock_out_at
            ? $attendance->work_date->copy()->setTimeFromTimeString($request->clock_out_at)
            : null;
        StampCorrectionRequest::create([
            'attendance_id'          => $request->attendance_id,
            'user_id'                => $attendance->user_id,
            'requested_clock_in_at'  => $clockIn,
            'requested_clock_out_at' => $clockOut,
            'requested_note'         => $request->note,
            'status'                 => StampCorrectionRequest::STATUS_APPROVED,
            'type'                   => StampCorrectionRequest::TYPE_ADMIN,
        ]);

        return redirect()
            ->back();
    }

    public function approve(Request $request, $id)
    {
        DB::transaction(function () use ($id) {

            $correctionRequest = StampCorrectionRequest::with([
                'attendance',
                'stampCorrectionBreaks',
            ])->findOrFail($id);

            if ($correctionRequest->status !== 'pending') {
                abort(403, 'この申請はすでに処理されています');
            }

            if (
                is_null($correctionRequest->requested_clock_in_at) &&
                is_null($correctionRequest->requested_clock_out_at) &&
                is_null($correctionRequest->requested_note) &&
                $correctionRequest->stampCorrectionBreaks->isEmpty()
            ) {
                abort(400, '修正内容が存在しません');
            }
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

    public function update(Request $request, $id)
    {
        DB::transaction(function () use ($request, $id) {

            $correctionRequest = StampCorrectionRequest::findOrFail($id);

            $attendance = $correctionRequest->attendance;

            $clockIn = $request->clock_in_at
                ? $attendance->work_date->copy()->setTimeFromTimeString($request->clock_in_at)
                : null;

            $clockOut = $request->clock_out_at
                ? $attendance->work_date->copy()->setTimeFromTimeString($request->clock_out_at)
                : null;

            $correctionRequest->update([
                'requested_clock_in_at'  => $clockIn,
                'requested_clock_out_at' => $clockOut,
                'requested_note'         => $request->note,
                'status' => 'approved',
            ]);

            $attendance->update([
                'clock_in_at'  => $clockIn,
                'clock_out_at' => $clockOut,
                'note'         => $request->note,
            ]);

            AttendanceBreak::where('attendance_id', $attendance->id)->delete();

            foreach ($correctionRequest->stampCorrectionBreaks as $correctionBreak) {

                AttendanceBreak::create([
                    'attendance_id'  => $attendance->id,
                    'break_start_at' => $correctionBreak->break_start_at,
                    'break_end_at'   => $correctionBreak->break_end_at,
                ]);
            }
        });
        return redirect()->route('admin.stamp_correction_request.index');
    }
}