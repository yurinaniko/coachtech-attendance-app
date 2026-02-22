<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;

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

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();
        if ($attendance) {
            return back()->withErrors([
                'clock_in' => '既に出勤しています'
            ]);
        }

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

        $latestBreak = $attendance->breaks()
        ->whereNull('break_end_at')
        ->latest()
        ->first();

        if ($latestBreak) {
            return back()->withErrors([
                'clock_out' => '休憩中は退勤できません'
            ]);
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
            ->firstOrFail();

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
            ->firstOrFail();
        $latestBreak = $attendance->breaks()
        ->whereNull('break_end_at')
        ->latest()
        ->firstOrFail();
        $latestBreak->update([
            'break_end_at' => now(),
        ]);
        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $month = $request->input('month')
            ? Carbon::createFromFormat('Y-m', $request->month): now();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth   = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn ($attendance) => $attendance->work_date->format('Y-m-d'));
        return view('attendance.list', compact('attendances', 'month'));
    }

    public function detail(Attendance $attendance,Request $request)
    {
        $latestRequest = $attendance->latestStampRequest();

        $disabled = false;
        $notice = null;

        if ($latestRequest && $latestRequest->status === StampCorrectionRequest::STATUS_PENDING) {
        $disabled = true;
        $notice = '※承認待ちのため修正できません。';
        }

        $breaks = $attendance->breaks()
        ->orderBy('break_start_at')
        ->get();

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
        ->where('status', StampCorrectionRequest::STATUS_PENDING)
        ->latest()
        ->first();

        $disabled = false;
        $notice = null;

        if ($pendingRequest) {
            $disabled = true;
            $notice = '※承認待ちのため修正できません。';
        }

        $displayCount = min($breaks->count() + 1, 5);

        return view('attendance.detail', [
            'attendance'     => $attendance,
            'breaks'         => $breaks,
            'displayCount'   => $displayCount,
            'disabled'       => $disabled,
            'notice'         => $notice,
            'pendingRequest' => $pendingRequest,
        ]);
    }

    public function detailByDate($date)
    {
        $user = auth()->user();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id'   => $user->id,
                'work_date' => $date,
            ]
        );

        $breaks = $attendance->breaks()
        ->orderBy('break_start_at')
        ->get();

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
        ->where('status', StampCorrectionRequest::STATUS_PENDING)
        ->latest()
        ->first();

        $disabled = false;
        $notice   = null;

        if ($pendingRequest) {
            $disabled = true;
            $notice = '※承認待ちのため修正できません。';
        }

        $displayCount = min($breaks->count() + 1, 5);

        return view('attendance.detail', [
            'attendance'     => $attendance,
            'breaks'         => $breaks,
            'displayCount'   => $displayCount,
            'pendingRequest' => $pendingRequest,
            'disabled'       => $disabled,
            'notice'         => $notice,
        ]);
    }
}
