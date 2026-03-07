@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧')
@section('main_class', 'admin-staff-attendance-page')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">

    <section class="admin-staff-attendance-card">
        <h1 class="admin-staff-attendance-title">{{ $staff->name }}さんの勤怠</h1>

        <div class="admin-staff-attendance-month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $prevMonth]) }}" class="admin-staff-attendance-month-link">← 前月</a>
            <p class="admin-staff-attendance-month-label">
                <img src="{{ asset('images/calendar.png') }}" alt="" class="admin-staff-attendance-month-icon">
                <span>{{ $currentMonth->format('Y/m') }}</span>
            </p>
            <a href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}" class="admin-staff-attendance-month-link">翌月 →</a>
        </div>

        <div class="admin-staff-attendance-table-wrap">
            <table class="admin-staff-attendance-table">
                <thead>
                    <tr>
                        <th>日付</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        @php
                            $date = \Carbon\Carbon::parse($attendance->work_date);
                            $weekday = ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek];
                        @endphp
                        <tr>
                            <td>{{ $date->format('m/d') }}({{ $weekday }})</td>
                            <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                            <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                            <td>{{ $attendance->break_total_label }}</td>
                            <td>{{ $attendance->work_total_label }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="admin-staff-attendance-detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-staff-attendance-empty">対象の勤怠データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-staff-attendance-actions">
        <a  href="{{ route('admin.attendance.staff.csv', ['id' => $staff->id, 'month' => $currentMonth->format('Y-m')]) }}"
            class="admin-staff-attendance-csv"
        >
            CSV出力
        </a>
</div>
    </section>
@endsection
