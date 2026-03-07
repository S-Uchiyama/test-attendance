@extends('layouts.app')

@section('title', '勤怠詳細')
@section('main_class', 'admin-attendance-detail-page')

@php
    $workDate = \Carbon\Carbon::parse($attendance->work_date);
    $breaks = $attendance->breaks->values();
    $displayBreakCount = max($breaks->count() + 1, 1);
@endphp

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">

    <section class="admin-attendance-detail-card">
        <h1 class="admin-attendance-detail-title">勤怠詳細</h1>

        <div class="admin-attendance-detail-panel">
            <div class="admin-attendance-detail-row">
                <div class="admin-attendance-detail-label">名前</div>
                <div class="admin-attendance-detail-value">{{ optional($attendance->user)->name }}</div>
            </div>

            <div class="admin-attendance-detail-row">
                <div class="admin-attendance-detail-label">日付</div>
                <div class="admin-attendance-detail-date">
                    <span>{{ $workDate->format('Y年') }}</span>
                    <span>{{ $workDate->format('n月j日') }}</span>
                </div>
            </div>

            <div class="admin-attendance-detail-row">
                <div class="admin-attendance-detail-label">出勤・退勤</div>
                <div class="admin-attendance-detail-time-range">
                    <input type="text" value="{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}">
                    <span>〜</span>
                    <input type="text" value="{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}">
                </div>
            </div>

            @for ($i = 0; $i < $displayBreakCount; $i++)
                @php
                    $break = $breaks->get($i);
                @endphp
                <div class="admin-attendance-detail-row">
                    <div class="admin-attendance-detail-label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</div>
                    <div class="admin-attendance-detail-time-range">
                        <input type="text" value="{{ $break && $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}">
                        <span>〜</span>
                        <input type="text" value="{{ $break && $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}">
                    </div>
                </div>
            @endfor

            <div class="admin-attendance-detail-row admin-attendance-detail-row-note">
                <div class="admin-attendance-detail-label">備考</div>
                <div class="admin-attendance-detail-note-wrap">
                    <textarea>{{ $attendance->note }}</textarea>
                </div>
            </div>
        </div>

        <div class="admin-attendance-detail-action">
            <button type="button" class="admin-attendance-detail-submit">修正</button>
        </div>
    </section>
@endsection
