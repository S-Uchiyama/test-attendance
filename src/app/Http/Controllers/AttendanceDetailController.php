<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function show(Request $request, int $id)
    {
        $attendance = Attendance::with('breaks')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $requestId = $request->query('request_id');

        $targetRequest = null;
        if ($requestId) {
            $targetRequest = $attendance->correctionRequests()
                ->where('id', $requestId)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        if (!$targetRequest) {
            $targetRequest = $attendance->correctionRequests()
                ->where('user_id', $request->user()->id)
                ->latest()
                ->first();
        }

        $status = optional($targetRequest)->status;
        $isLocked = in_array($status, ['pending', 'approved'], true);

        $lockMessage = null;
        if ($status === 'pending') {
            $lockMessage = '＊承認待ちのため修正はできません。';
        } elseif ($status === 'approved') {
            $lockMessage = '＊承認済みのため修正はできません。';
        }

        $displayClockIn = $attendance->clock_in_label;
        $displayClockOut = $attendance->clock_out_label;
        $displayBreaks = $attendance->breaks->values();
        $displayReason = $attendance->note;

        if ($targetRequest) {
            $displayClockIn = $targetRequest->requested_clock_in
                ? \Carbon\Carbon::parse($targetRequest->requested_clock_in)->format('H:i')
                : '';
            $displayClockOut = $targetRequest->requested_clock_out
                ? \Carbon\Carbon::parse($targetRequest->requested_clock_out)->format('H:i')
                : '';
            $displayBreaks = $targetRequest->breaks()->get();
            $displayReason = $targetRequest->reason;
        }

        return view('attendance.detail', compact(
            'attendance',
            'isLocked',
            'lockMessage',
            'displayClockIn',
            'displayClockOut',
            'displayBreaks',
            'displayReason'
        ));
    }
}
