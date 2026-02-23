<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = \App\Models\User::where('role','user')->get();

        foreach($users as $user){
            for($i = 0; $i<5; $i++){
                $date = now()->subDays($i)->toDateString();

                $attendance = \App\Models\Attendance::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'work_date' => $date,
                    ],
                    [
                        'clock_in' => '09:00:00',
                        'clock_out' => '18:00:00',
                        'status' => 'done',
                        'note' => '通常勤務',
                    ]
                );

                \App\Models\AttendanceBreak::updateOrCreate(
                    [
                        'attendance_id' => $attendance->id,
                        'break_start' => '12:00:00',
                    ],
                    [
                        'break_end' => '13:00:00',
                    ]
                );
            }
        }
    }
}
