@extends('layouts.app')

@section('title', '勤怠一覧')
@section('main_class', 'attendance-page')

@php
    $currentMonth = isset($currentMonth)
        ? \Carbon\Carbon::parse($currentMonth)
        : now()->startOfMonth();
@endphp

@section('content')
    <link rel="stylesheet" href="{{ asset('css/attendance/list.css') }}">

    <section class="attendance-list-card">
        <h1 class="attendance-list-title">勤怠一覧</h1>

        <div class="attendance-list-month-nav">
            <a
                href="{{ route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}"
                class="attendance-list-month-link"
            >
                ← 前月
            </a>

            <p class="attendance-list-month-label">
                <img src="{{ asset('images/calendar.png') }}" alt="" class="attendance-list-month-icon">
                <span>{{ $currentMonth->format('Y/m') }}</span>
            </p>

            <a
                href="{{ route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}"
                class="attendance-list-month-link"
            >
                翌月 →
            </a>
        </div>

        <div class="attendance-list-table-wrap">
            <table class="attendance-list-table">
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
                        <tr>
                            <td>{{ $attendance->work_date_jp_label }}</td>
                            <td>{{ $attendance->clock_in_label }}</td>
                            <td>{{ $attendance->clock_out_label }}</td>
                            <td>{{ $attendance->break_total_label }}</td>
                            <td>{{ $attendance->work_total_label }}</td>
                            <td>
                                @if ($attendance->id)
                                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="attendance-list-detail-link">詳細</a>
                                @else
                                    <span class="attendance-list-detail-link">詳細</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="attendance-list-empty">対象の勤怠データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
