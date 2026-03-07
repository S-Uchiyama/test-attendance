<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function show(Request $request, int $id)
    {
        $attendance = Attendance::with(['user', 'breaks'])
            ->findOrFail($id);

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
        ]);
    }
}
