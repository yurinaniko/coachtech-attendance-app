<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance) {
            return view('attendance.index', [
                'user' => $user,
                'today' => $today,
                'status' => 'before_work'
            ]);
        }
        $latestBreak = $attendance
        ? $attendance->breaks()
            ->whereNull('break_end_at')
            ->orderByDesc('break_start_at')
            ->first()
        : null;

        if (!$attendance || !$attendance->clock_in_at) {
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

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today
            ]
        );

        if ($attendance->clock_in_at) {
            return back()->withErrors([
                'clock_in' => '既に出勤しています'
            ]);
        }

        $attendance->update([
            'clock_in_at' => now()
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->first();

        if (!$attendance) {
            return back()->withErrors([
                'clock_out' => '出勤していないため退勤できません'
            ]);
        }

        if ($attendance->clock_out_at) {
            return back()->withErrors([
                'clock_out' => '既に退勤済みです'
            ]);
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

        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->first();

        if (!$attendance) {
            return back()->withErrors([
                'break_start' => '出勤していないため休憩できません'
            ]);
        }

        $activeBreak = $attendance->breaks()
            ->whereNull('break_end_at')
            ->exists();

        if ($activeBreak) {
            return back();
        }

        $attendance->breaks()->create([
            'break_start_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $user = auth()->user();
        $today = now()->toDateString();
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->whereNotNull('clock_in_at')
            ->whereNull('clock_out_at')
            ->firstOrFail();
        $latestBreak = $attendance->breaks()
            ->whereNull('break_end_at')
            ->orderBy('break_start_at')
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
            ->with('breaks')
            ->orderBy('work_date')
            ->get()
            ->groupBy(fn ($a) => $a->work_date->format('Y-m-d'))
            ->map(fn ($items) => $items->first());

        return view('attendance.list', compact('attendances', 'month'));
    }

    public function detail($date)
    {
        $user = auth()->user();
        $isFuture = Carbon::parse($date)->isFuture();


        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $date)
            ->first();

        if (!$attendance && !$isFuture) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date,
            ]);
        }

        $breaks = collect();
        $pendingRequest = null;
        $notice = null;

        if ($attendance) {

            // 休憩取得
            $breaks = $attendance->breaks()
                ->orderBy('break_start_at')
                ->get();

            // 最新申請
            $pendingRequest = $attendance->latestStampRequest();

            if ($pendingRequest && $pendingRequest->status === StampCorrectionRequest::STATUS_PENDING) {
                $notice = '※承認待ちのため修正できません。';
            }
        }

        if ($isFuture) {
            $notice = '※未来日のため修正できません。';
        }

        $displayCount = min($breaks->count() + 1, 5);

        return view('attendance.detail', [
            'attendance' => $attendance,
            'breaks' => $breaks,
            'displayCount' => $displayCount,
            'pendingRequest' => $pendingRequest,
            'notice' => $notice,
            'isFuture' => $isFuture,
            'targetDate' => $attendance?->work_date ?? Carbon::parse($date)
        ]);
    }
}
