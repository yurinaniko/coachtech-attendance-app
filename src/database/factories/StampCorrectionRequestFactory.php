<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\User;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition()
    {
        $user = User::factory()->create();
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory()->create([
                'user_id' => $user->id,
            ])->id,
            'requested_clock_in_at'  => '09:00',
            'requested_clock_out_at' => '18:00',
            'requested_note' => 'テスト申請',
            'status' => 'pending',
        ];
    }
}