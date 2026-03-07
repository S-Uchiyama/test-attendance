<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceStaffController extends Controller
{
    public function index(Request $request, int $id)
    {
        $staff = User::query()
            ->where('role', 'user')
            ->findOrFail($id);

        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        return view('admin.attendance.staff', [
            'staff' => $staff,
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function exportCsv(Request $request, int $id)
    {
        $staff = User::query()
            ->where('role', 'user')
            ->findOrFail($id);

        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        $fileName = $staff->name . '_' . $currentMonth->format('Y_m') . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return response()->stream(function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            // Excel文字化け対策(BOM)
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $date = Carbon::parse($attendance->work_date);
                $weekday = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];

                fputcsv($stream, [
                    $date->format('Y/m/d') . '(' . $weekday . ')',
                    $attendance->clock_in ? Carbon::parse($attendance->clock_in)->format('H:i') : '',
                    $attendance->clock_out ? Carbon::parse($attendance->clock_out)->format('H:i') : '',
                    $attendance->break_total_label,
                    $attendance->work_total_label,
                ]);
            }

            fclose($stream);
        }, 200, $headers);
    }
}
