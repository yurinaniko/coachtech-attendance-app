<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('is_admin', false)->get();

        foreach ($users as $user) {

            // 今月5日分
            for ($i = 1; $i <= 20; $i++) {

                Attendance::create([
                    'user_id'      => $user->id,
                    'work_date'    => Carbon::now()->subDays($i),
                    'clock_in_at'  => Carbon::createFromTime(rand(8,10), rand(0,59)),
                    'clock_out_at' => Carbon::createFromTime(rand(17,19), rand(0,59)),
                    'note' => collect([
                            '通常勤務','リモート勤務','外出あり','会議対応',
                    ])->random(),
                    'status'       => 'approved',
                ]);
            }

            for ($i = 1; $i <= 20; $i++) {

                Attendance::create([
                    'user_id'      => $user->id,
                    'work_date'    => Carbon::now()->subMonth()->subDays($i),
                    'clock_in_at'  => Carbon::createFromTime(rand(8,10), rand(0,59)),
                    'clock_out_at' => Carbon::createFromTime(rand(17,19), rand(0,59)),
                    'note' => collect([
                            '通常勤務','リモート勤務','外出あり','会議対応',
                    ])->random(),
                    'status'       => 'approved',
                ]);
            }
        }
    }
}
