<?php

namespace App\Http\Controllers\Admin;

use Symfony\Component\HttpFoundation\StreamedResponse;
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

    public function csv(User $user, Request $request): StreamedResponse
    {
        $month = Carbon::parse($request->month)->startOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [
                $month->copy()->startOfMonth(),
                $month->copy()->endOfMonth(),
            ])
            ->orderBy('work_date')
            ->get();

        $response = new StreamedResponse(function () use ($attendances) {

            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩時間',
                '勤務時間',
            ]);

            foreach ($attendances as $attendance) {

                fputcsv($handle, [
                    $attendance->work_date->format('Y/m/d'),
                    optional($attendance->clock_in_at)->format('H:i') ?? '-',
                    optional($attendance->clock_out_at)->format('H:i') ?? '-',
                    $attendance->break_time_hhmm ?? '-',
                    $attendance->work_time_hhmm ?? '-',
                ]);
            }

            fclose($handle);
        });

        $fileName = "{$user->name}_{$month->format('Y-m')}.csv";

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            "attachment; filename=\"{$fileName}\""
        );

        return $response;
    }
}
