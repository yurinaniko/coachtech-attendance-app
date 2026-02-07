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

    public function show($id)
    {
        $request = StampCorrectionRequest::with([
            'user',
            'attendance',
            'attendance.breaks'
        ])->findOrFail($id);

        return view('admin.stamp_correction_requests.show', compact('request'));
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

            $correctionRequest = StampCorrectionRequest::with('attendance')
                ->findOrFail($id);

            $attendance = $correctionRequest->attendance;

            $attendance->update([
                'clock_in_at'  => $correctionRequest->clock_in_at,
                'clock_out_at' => $correctionRequest->clock_out_at,
                'note'         => $correctionRequest->reason,
            ]);

            $correctionRequest->update([
                'status' => 'approved',
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_requests.index')
            ->with('success', '申請を承認しました');
    }
}