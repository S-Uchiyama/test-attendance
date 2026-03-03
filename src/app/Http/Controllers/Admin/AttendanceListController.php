<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceListController extends Controller
{
     public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());
        $currentDate = Carbon::parse($date)->startOfDay();

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $currentDate->toDateString())
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'currentDate' => $currentDate,
            'prevDate' => $currentDate->copy()->subDay()->toDateString(),
            'nextDate' => $currentDate->copy()->addDay()->toDateString(),
        ]);
    }
}
