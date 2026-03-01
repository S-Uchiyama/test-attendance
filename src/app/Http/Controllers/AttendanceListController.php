<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $request->user()->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        return view('attendance.list', [
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
        ]);
    }
}
