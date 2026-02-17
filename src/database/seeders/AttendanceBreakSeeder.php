<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;

class AttendanceBreakSeeder extends Seeder
{
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {

            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => Carbon::createFromTime(12, 0),
                'break_end_at'   => Carbon::createFromTime(13, 0),
            ]);
        }
    }
}
