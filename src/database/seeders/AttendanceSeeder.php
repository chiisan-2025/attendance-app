<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $user=User::where('role','user')->first();

        Attendance::create([
            'user_id'=>$user->id,
            'work_date'=>today(),
            'clock_in_at'=>'09:00:00',
            'clock_out_at'=>'18:00:00',
            'status'=>'退勤済',
        ]);
    }
}