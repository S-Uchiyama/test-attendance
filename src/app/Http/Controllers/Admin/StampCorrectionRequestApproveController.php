<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestApproveController extends Controller
{
    public function show(Request $request, int $attendance_correct_request_id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with(['user', 'breaks', 'attendance'])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.stamp_correction_request.approve', [
            'correctionRequest' => $correctionRequest,
        ]);
    }

    public function approve(Request $request, int $attendance_correct_request_id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with(['attendance', 'breaks'])
            ->findOrFail($attendance_correct_request_id);

        // サーバー側の二重承認防止
        if ($correctionRequest->status === 'approved') {
            return redirect()
                ->route('admin.stamp_correction_request.approve', [
                    'attendance_correct_request_id' => $correctionRequest->id,
                ]);
        }

        DB::transaction(function () use ($request, $correctionRequest) {
            $attendance = $correctionRequest->attendance;

            // 勤怠が未作成日の申請に備えて作成
            if (!$attendance) {
                $attendance = Attendance::create([
                    'user_id' => $correctionRequest->user_id,
                    'work_date' => $correctionRequest->target_date,
                    'clock_in' => null,
                    'clock_out' => null,
                    'status' => 'off',
                    'note' => null,
                ]);

                $correctionRequest->attendance_id = $attendance->id;
            }

            $attendance->clock_in = $correctionRequest->requested_clock_in;
            $attendance->clock_out = $correctionRequest->requested_clock_out;
            $attendance->note = $correctionRequest->reason;
            $attendance->status = $attendance->clock_out ? 'done' : ($attendance->clock_in ? 'working' : 'off');
            $attendance->save();

            AttendanceBreak::where('attendance_id', $attendance->id)->delete();

            foreach ($correctionRequest->breaks as $break) {
                AttendanceBreak::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $break->break_start,
                    'break_end' => $break->break_end,
                ]);
            }

            $correctionRequest->status = 'approved';
            $correctionRequest->approved_by = $request->user()->id;
            $correctionRequest->approved_at = now();
            $correctionRequest->save();
        });

        return redirect()
            ->route('admin.stamp_correction_request.approve', [
                'attendance_correct_request_id' => $correctionRequest->id,
            ]);
    }
}
