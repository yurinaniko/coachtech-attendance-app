<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    public function index(Request $request, User $user)
    {
        $month = Carbon::parse(
            $request->query('month', now()->format('Y-m'))
        );

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get()
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

        return view('admin.staff.attendance', compact(
            'user', 'attendances', 'month'
        ));
    }

    public function csv(Request $request, User $user)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        // CSVレスポンス生成（ここは次のステップでOK）
    }
}
