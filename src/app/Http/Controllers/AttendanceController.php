<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();

        $attendance = Attendance::with('breaks')
            ->where('user_id', $request->user()->id)
            ->where('work_date', $today)
            ->first();

        // 勤怠データから現在ステータスを判定
        if (!$attendance) {
            $status = '勤務外';
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } elseif ($attendance->breaks->whereNull('break_end')->isNotEmpty()) {
            $status = '休憩中';
        } else {
            $status = '出勤中';
        }

        return view('attendance.index', [
            'status' => $status,
        ]);
    }

    public function clockIn(Request $request)
    {
        $today = now()->toDateString();

        // 1日1回だけ出勤を作成
        Attendance::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'work_date' => $today,
            ],
            [
                'clock_in' => now()->format('H:i:s'),
                'status' => 'working',
            ]
        );

        return redirect()->route('attendance.index');
    }

    public function clockOut(Request $request)
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('work_date', $today)
            ->first();

        // 出勤中 or 休憩中のときだけ退勤
        if (!$attendance || $attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        // 休憩中なら休憩終了時刻を補完してから退勤
        $openBreak = $attendance->breaks()->whereNull('break_end')->first();
        if ($openBreak) {
            $openBreak->update([
                'break_end' => now()->format('H:i:s'),
            ]);
        }

        $attendance->update([
            'clock_out' => now()->format('H:i:s'),
            'status' => 'done',
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakIn(Request $request)
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('work_date', $today)
            ->first();

        // 出勤中のときだけ休憩入を作成
        if (!$attendance || $attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        // 休憩中（未終了休憩あり）なら二重作成しない
        if ($attendance->breaks()->whereNull('break_end')->exists()) {
            return redirect()->route('attendance.index');
        }

        $attendance->breaks()->create([
            'break_start' => now()->format('H:i:s'),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakOut(Request $request)
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || $attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        $openBreak = $attendance->breaks()->whereNull('break_end')->first();

        // 休憩中でない場合は何もしない
        if (!$openBreak) {
            return redirect()->route('attendance.index');
        }

        $openBreak->update([
            'break_end' => now()->format('H:i:s'),
        ]);

        return redirect()->route('attendance.index');
    }

}
