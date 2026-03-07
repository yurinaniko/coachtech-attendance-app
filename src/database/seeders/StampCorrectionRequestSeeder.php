<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionBreak;

class StampCorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::has('breaks')
            ->with('breaks')
            ->inRandomOrder()
            ->take(5)
            ->get();

        foreach ($attendances as $attendance) {

            $request = StampCorrectionRequest::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                 // 修正申請なので少し時間をズラす
                'requested_clock_in_at' => optional($attendance->clock_in_at)?->copy()->addMinutes(10),
                'requested_clock_out_at' => $attendance->clock_out_at->copy()->addMinutes(10),
                'requested_note' => collect(['電車遅延のため','打刻忘れ','外出戻り打刻ミス','遅刻修正',])->random(),
                'status' => 'pending',
            ]);

            // 休憩時間は5分ずらして申請用休憩を作る
            foreach ($attendance->breaks as $break) {

                StampCorrectionBreak::create([
                    'stamp_correction_request_id' => $request->id,
                    'attendance_break_id' => $break->id,
                    'break_start_at' => $break->break_start_at->copy()->addMinutes(5),
                    'break_end_at' => $break->break_end_at->copy()->addMinutes(5),
                ]);

            }
        }
    }
}
