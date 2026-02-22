<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'clock_out_at' => null,
        ];
    }
}
