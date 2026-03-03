@extends('layouts.app')

@section('title', '勤怠一覧')
@section('main_class', 'admin-attendance-list-page')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">

    <section class="admin-attendance-list-card">
        <h1 class="admin-attendance-list-title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>

        <div class="admin-attendance-month-nav">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="admin-attendance-month-link">← 前日</a>
            <p class="admin-attendance-month-label">
                <img src="{{ asset('images/calendar.png') }}" alt="" class="admin-attendance-month-icon">
                <span>{{ $currentDate->format('Y/m/d') }}</span>
            </p>
            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="admin-attendance-month-link">翌日 →</a>
        </div>

        <div class="admin-attendance-table-wrap">
            <table class="admin-attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td>{{ optional($attendance->user)->name }}</td>
                            <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                            <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                            <td>{{ $attendance->break_total_label }}</td>
                            <td>{{ $attendance->work_total_label }}</td>
                            <td>
                                <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="admin-attendance-detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-attendance-empty">対象の勤怠データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
