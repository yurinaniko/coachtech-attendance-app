<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

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

    public function monthly(Request $request, User $user)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('work_date', [$start, $end])
        ->orderBy('work_date')
        ->get();

        return view(
        'admin.staff.attendance.index',
        compact('user', 'attendances', 'month')
        );
    }

    public function list(Request $request)
    {
        $date = Carbon::parse(
        $request->query('date', now()->toDateString())
        );

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', [
            'date' => $date,
            'attendances' => $attendances,
        ]);
    }

    public function detail($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $data = [];

        if ($request->filled('clock_in_at')) {
        $data['clock_in_at'] = $request->clock_in_at;
        }

        if ($request->filled('clock_out_at')) {
        $data['clock_out_at'] = $request->clock_out_at;
        }

        if ($request->filled('note')) {
        $data['note'] = $request->note;
        }

        $attendance->update($data);
        return redirect()
            ->route('admin.attendance.detail', $attendance->id)
            ->with('success', '勤怠を修正しました');
    }
}
