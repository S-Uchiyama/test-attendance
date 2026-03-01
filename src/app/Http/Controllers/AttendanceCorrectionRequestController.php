<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\DB;

class AttendanceCorrectionRequestController extends Controller
{
    public function store(StoreAttendanceCorrectionRequest $request, int $id)
    {
        $attendance = Attendance::with('breaks')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        DB::transaction(function () use ($request, $attendance) {
            $correctionRequest = AttendanceCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $request->user()->id,
                'target_date' => $attendance->work_date,
                'requested_clock_in' => $request->input('clock_in'),
                'requested_clock_out' => $request->input('clock_out'),
                'reason' => $request->input('reason'),
                'status' => 'pending',
            ]);

            foreach ($request->input('breaks', []) as $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                if (!$start || !$end) {
                    continue;
                }

                $correctionRequest->breaks()->create([
                    'break_start' => $start,
                    'break_end' => $end,
                ]);
            }
        });

        return redirect()
            ->route('attendance.detail', ['id' => $attendance->id])
            ->with('status', '修正申請を送信しました');
    }
}
