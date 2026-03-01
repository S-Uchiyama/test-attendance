<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');
        $status = in_array($status, ['pending', 'approved'], true) ? $status : 'pending';

        $requests = AttendanceCorrectionRequest::with('user')
            ->where('user_id', $request->user()->id)
            ->where('status', $status)
            ->latest()
            ->get();

        return view('stamp_correction_request.list', [
            'requests' => $requests,
            'status' => $status,
        ]);
    }
}
