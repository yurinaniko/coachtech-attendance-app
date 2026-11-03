<?php

namespace App\Http\Controllers\Admin;

use Symfony\Component\HttpFoundation\HeaderUtils;
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
        $month = $this->parseDateOrFail(
            $request->query('month', now()->format('Y-m')), 'Y-m'
        );

        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $dates = [];
        $current = $start->copy();

        while ($current <= $end) {

            $attendance = Attendance::firstOrCreate([
                'user_id'   => $user->id,
                'work_date' => $current->toDateString(),
            ]);

            $dates[] = $attendance;
            $current->addDay();
        }

        $attendances = collect($dates)
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));
        return view('admin.staff.attendance', compact(
            'user', 'attendances', 'month'
        ));
    }

    public function csv(User $user, Request $request): StreamedResponse
    {
        $month = $this->parseDateOrFail(
            $request->query('month', now()->format('Y-m')), 'Y-m'
        )->startOfMonth();
        $start = $month->copy()->startOfMonth();
        $end   = $month->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->whereNotNull('clock_in_at')
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

        // 氏名は利用者が登録時に自由入力する値。そのまま "..." に埋め込むと
        // 引用符で区切りが壊れ、日本語名もヘッダとして不正になるため、RFC 6266 形式で出す。
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set(
            'Content-Disposition',
            HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $fileName,
                "attendance_{$month->format('Y-m')}.csv"
            )
        );

        return $response;
    }
}
