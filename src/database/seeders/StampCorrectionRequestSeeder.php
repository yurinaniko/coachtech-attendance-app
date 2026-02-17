<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::inRandomOrder()->take(5)->get();

        foreach ($attendances as $attendance) {

            StampCorrectionRequest::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'requested_clock_in_at'  => $attendance->clock_in_at,
                'requested_clock_out_at' => $attendance->clock_out_at,
                'requested_note' => '遅刻したため',
                'status' => 'pending',
            ]);
        }
    }
}
