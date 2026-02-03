<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
        ->whereDate('work_date', $today)
        ->first();
        $latestBreak = $attendance
        ? $attendance->breaks()->latest()->first(): null;

        if (!$attendance) {
            $status = 'before_work';
        } elseif ($attendance->clock_out_at) {
            $status = 'after_work';
        } elseif (
            $latestBreak &&
            $latestBreak->break_start_at &&
            !$latestBreak->break_end_at
        ) {
            $status = 'on_break';
        } else {
            $status = 'working';
        }

        return view('attendance.index', compact('user', 'today', 'status'));
    }

    public function clockIn()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
        ->whereDate('work_date', now()->toDateString())
        ->firstOrFail();

        if ($attendance->clock_out_at) {
        return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }


    public function breakStart()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $attendance->breaks()->create([
            'break_start_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();
        $latestBreak = $attendance->breaks()->latest()->first();
        $latestBreak->update([
            'break_end_at' => now(),
        ]);
        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $user = auth()->user();

        // 表示対象の年月（URLパラメータがなければ今月）
        $month = $request->get('month', now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $month);

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('work_date', $carbonMonth->year)
            ->whereMonth('work_date', $carbonMonth->month)
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

        return view('attendance.list', [
            'month' => $carbonMonth,
            'attendances' => $attendances,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
        ->where('user_id', auth()->id())
        ->findOrFail($id);

        $breaks = $attendance->breaks;

        return view('attendance.detail', [
            'attendance' => $attendance,
            'break1' => $breaks->get(0),
            'break2' => $breaks->get(1),
        ]);
    }
}
