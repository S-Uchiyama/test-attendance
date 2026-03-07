<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateAttendanceRequest;
use App\Models\Attendance;

class AttendanceDetailController extends Controller
{
    public function show(int $id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
        ]);
    }

    public function update(AdminUpdateAttendanceRequest $request, int $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);
        $validated = $request->validated();

        \Illuminate\Support\Facades\DB::transaction(function () use ($attendance, $validated) {
            $attendance->clock_in = $validated['clock_in'] ?? null;
            $attendance->clock_out = $validated['clock_out'] ?? null;
            $attendance->note = $validated['note'];
            $attendance->status = $attendance->clock_out ? 'done' : ($attendance->clock_in ? 'working' : 'off');
            $attendance->save();

            $attendance->breaks()->delete();

            foreach ($validated['breaks'] ?? [] as $break) {
                if (empty($break['start']) || empty($break['end'])) {
                    continue;
                }

                $attendance->breaks()->create([
                    'break_start' => $break['start'],
                    'break_end' => $break['end'],
                ]);
            }
        });

        return redirect()
            ->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('status', '修正しました。');
    }

}
