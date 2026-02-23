<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AttendanceCorrectionRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = \App\Models\User::where('role','admin')->first();
        $attendances = \App\Models\Attendance::with('user')
            ->whereHas('user',function($q){
                $q->where('role','user');
            })
            ->take(2)
            ->get();

        if($attendances->count() < 2 || !$admin){
            return;
        }

        $pendingAttendance = $attendances[0];
        $approvedAttendance = $attendances[1];

        $pending = \App\Models\AttendanceCorrectionRequest::updateOrCreate(
            [
                'attendance_id' => $pendingAttendance->id,
                'user_id' => $pendingAttendance->user_id,
                'target_date' => $pendingAttendance->work_date,
                'status' => 'pending',
            ],
            [
                'requested_clock_in' => '09:30:00',
                'requested_clock_out' => '18:30:00',
                'reason' => '電車遅延のため',
                'approved_by' => null,
                'approved_at' => null,
            ]
        );

        \App\Models\AttendanceCorrectionRequestBreak::updateOrCreate(
            [
                'attendance_correction_request_id' => $pending->id,
                'break_start' => '12:30:00',
            ],
            [
                'break_end' => '13:30:00',
            ]
        );

        $approved = \App\Models\AttendanceCorrectionRequest::updateOrCreate(
            [
                'attendance_id' => $approvedAttendance->id,
                'user_id' => $approvedAttendance->user_id,
                'target_date' => $approvedAttendance->work_date,
                'status' => 'approved',
            ],
            [
                'requested_clock_in' => '08:45:00',
                'requested_clock_out' => '17:45:00',
                'reason' => '打刻漏れ修正',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ]
        );

        \App\Models\AttendanceCorrectionRequestBreak::updateOrCreate(
            [
                'attendance_correction_request_id' => $approved->id,
                'break_start' => '12:00:00',
            ],
            [
                'break_end' => '13:00:00',
            ]
        );
    }
}
