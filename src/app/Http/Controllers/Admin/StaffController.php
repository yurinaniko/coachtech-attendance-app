<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffController extends Controller
{
    public function list()
    {
        $users = User::orderBy('id')->get();

        return view('admin.staff.list', compact('users'));
    }

    public function store(Request $request)
    {
        StampCorrectionRequest::create([
            'attendance_id' => $request->attendance_id,
            'user_id'       => auth()->id(),
            'status'        => 'pending',
        ]);

        return redirect()
            ->route('attendance.list')
            ->with('message', '修正申請を送信しました');
    }

    public function approve($id)
    {
        $request = StampCorrectionRequest::with('attendance')->findOrFail($id);
        $request->attendance->update([
            'clock_in_at'  => $request->clock_in_at,
            'clock_out_at' => $request->clock_out_at,
            'note'         => $request->note,
        ]);

        $request->update(['status' => 'approved']);
        return redirect()
            ->route('admin.stamp_correction_requests.index');
    }
}
