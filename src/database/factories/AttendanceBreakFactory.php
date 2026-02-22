<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceBreak;

class AttendanceBreakFactory extends Factory
{
    protected $model = AttendanceBreak::class;
    public function definition()
    {
        return [
            'attendance_id' => \App\Models\Attendance::factory(),
            'break_start_at' => now(),
            'break_end_at' => null,
        ];
    }
}
