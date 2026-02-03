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
        $month = Carbon::parse($request->input('month', now()->format('Y-m')));
        $date = $month->copy()->startOfMonth();
        $users = User::with('attendances')->get();
        $attendances = Attendance::with('user')
            ->whereMonth('work_date', $month->month)
            ->whereYear('work_date', $month->year)
            ->get()
            ->groupBy('user_id');

        return view('admin.attendance.list', [
            'month' => $month,
            'date'  => $date,
            'users' => $users,
        ]);
    }
}
