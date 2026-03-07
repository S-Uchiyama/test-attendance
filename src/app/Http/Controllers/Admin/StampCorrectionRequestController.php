<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
     public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $status = in_array($status, ['pending', 'approved'], true) ? $status : 'pending';

        $requests = AttendanceCorrectionRequest::with('user')
            ->where('status', $status)
            ->latest()
            ->get();

        return view('admin.stamp_correction_request.list', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }
}
