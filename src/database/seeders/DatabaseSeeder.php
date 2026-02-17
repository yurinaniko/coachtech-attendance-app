<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AttendanceSeeder;
use Database\Seeders\AttendanceBreakSeeder;
use Database\Seeders\StampCorrectionRequestSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            UserSeeder::class,
            AttendanceSeeder::class,
            AttendanceBreakSeeder::class,
            StampCorrectionRequestSeeder::class,
        ]);
    }
}
