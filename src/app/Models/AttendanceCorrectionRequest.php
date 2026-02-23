<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'target_date',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class,'approved_by');
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceCorrectionRequestBreak::class);
    }
}
