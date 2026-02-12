<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_requests.index', compact('requests'));
    }

    public function edit($id)
    {
        $request = StampCorrectionRequest::with([
            'user',
            'attendance',
            'stampCorrectionBreaks.attendanceBreak',
        ])->findOrFail($id);

        return view('admin.stamp_correction_requests.edit', compact('request'));
    }

    public function store(Request $request)
    {
        StampCorrectionRequest::create([
            'attendance_id'          => $request->attendance_id,
            'user_id'                => auth()->id(),
            'requested_clock_in_at'  => $request->clock_in_at,
            'requested_clock_out_at' => $request->clock_out_at,
            'requested_note'         => $request->note,
            'status'                 => 'pending',
        ]);

        return redirect()
            ->back()
            ->with('success', '修正申請を送信しました');
    }

    public function approve(Request $request, $id)
    {
        DB::transaction(function () use ($id) {

            $correctionRequest = StampCorrectionRequest::with([
                'attendance',
                'stampCorrectionBreaks.attendanceBreak',
            ])->findOrFail($id);
            // 二重承認防止
            if ($correctionRequest->status !== 'pending') {
                abort(403, 'この申請はすでに処理されています');
            }
            // null ガード
            if (
                is_null($correctionRequest->requested_clock_in_at) &&
                is_null($correctionRequest->requested_clock_out_at)
            ) {
                abort(400, '修正内容が存在しません');
            }
            $attendance = $correctionRequest->attendance;

            $attendance->update([
                'clock_in_at'  => $correctionRequest->requested_clock_in_at,
                'clock_out_at' => $correctionRequest->requested_clock_out_at,
                'note'         => $correctionRequest->requested_note,
            ]);

            // ② 休憩を反映
            foreach ($correctionRequest->stampCorrectionBreaks as $correctionBreak) {
            $attendanceBreak = $correctionBreak->attendanceBreak;

            if ($attendanceBreak) {
                $attendanceBreak->update([
                    'break_start_at' => $correctionBreak->break_start_at,
                    'break_end_at'   => $correctionBreak->break_end_at,
                ]);
                }
            }

            $correctionRequest->update([
                'status' => 'approved',
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_requests.index')
            ->with('success', '申請を承認しました');
    }
}