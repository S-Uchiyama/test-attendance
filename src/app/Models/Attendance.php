<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    public function getClockInLabelAttribute(): string
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '';
    }

    public function getClockOutLabelAttribute(): string
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '';
    }

    public function getWorkDateJpLabelAttribute(): string
    {
        $date = Carbon::parse($this->work_date);
        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
        return $date->format('m/d') . '(' . $weekday . ')';
    }

    public function getBreakTotalMinutesAttribute(): int
    {
        return $this->breaks
            ->filter(fn ($break) => $break->break_start && $break->break_end)
            ->sum(fn ($break) => Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start)));
    }

    public function getBreakTotalLabelAttribute(): string
    {
        if (!$this->clock_in) {
            return '';
        }

        return sprintf(
            '%d:%02d',
            intdiv($this->break_total_minutes, 60),
            $this->break_total_minutes % 60
        );
    }

    public function getWorkTotalLabelAttribute(): string
    {
        if (!$this->clock_in || !$this->clock_out) {
            return '';
        }

        $workMinutes = Carbon::parse($this->clock_out)->diffInMinutes(Carbon::parse($this->clock_in)) - $this->break_total_minutes;
        $workMinutes = max($workMinutes, 0);

        return sprintf('%d:%02d', intdiv($workMinutes, 60), $workMinutes % 60);
    }
}
