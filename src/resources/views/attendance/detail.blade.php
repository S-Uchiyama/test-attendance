@extends('layouts.app')

@section('title', '勤怠詳細')
@section('main_class', 'attendance-detail-page')

@php
    $workDate = \Carbon\Carbon::parse($attendance->work_date);
    $breaks = collect($displayBreaks ?? [])->values();
    $displayBreakCount = max($breaks->count() + 1, 1);
@endphp

@section('content')
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}">

    <section class="attendance-detail-card">
        <h1 class="attendance-detail-title">勤怠詳細</h1>
        @if ($isLocked)
            <div class="attendance-detail-panel">
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">名前</div>
                    <div class="attendance-detail-value">{{ optional($attendance->user)->name ?? auth()->user()->name }}</div>
                </div>

                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">日付</div>
                    <div class="attendance-detail-date">
                        <span>{{ $workDate->format('Y年') }}</span>
                        <span>{{ $workDate->format('n月j日') }}</span>
                    </div>
                </div>

                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">出勤・退勤</div>
                    <div class="attendance-detail-time-range attendance-detail-time-range--plain">
                        <span>{{ $displayClockIn }}</span>
                        <span>〜</span>
                        <span>{{ $displayClockOut }}</span>
                    </div>
                </div>

                @foreach ($breaks as $i => $break)
                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</div>
                        <div class="attendance-detail-time-range attendance-detail-time-range--plain">
                            <span>{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</span>
                            <span>〜</span>
                            <span>{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
                        </div>
                    </div>
                @endforeach

                <div class="attendance-detail-row attendance-detail-row-note">
                    <div class="attendance-detail-label">備考</div>
                    <div class="attendance-detail-value attendance-detail-value--note">{{ $displayReason }}</div>
                </div>
            </div>

            <p class="attendance-detail-lock-message">{{ $lockMessage }}</p>
        @else
            <form action="{{ url('/attendance/detail/' . $attendance->id . '/request') }}" method="POST">
                @csrf

                <div class="attendance-detail-panel">
                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">名前</div>
                        <div class="attendance-detail-value">{{ optional($attendance->user)->name ?? auth()->user()->name }}</div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">日付</div>
                        <div class="attendance-detail-date">
                            <span>{{ $workDate->format('Y年') }}</span>
                            <span>{{ $workDate->format('n月j日') }}</span>
                        </div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">出勤・退勤</div>
                        <div class="attendance-detail-time-range">
                            <input type="text" name="clock_in" value="{{ old('clock_in', $displayClockIn) }}">
                            <span>〜</span>
                            <input type="text" name="clock_out" value="{{ old('clock_out', $displayClockOut) }}">
                        </div>
                    </div>

                    @for ($i = 0; $i < $displayBreakCount; $i++)
                        @php
                            $break = $breaks->get($i);
                        @endphp
                        <div class="attendance-detail-row">
                            <div class="attendance-detail-label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</div>
                            <div class="attendance-detail-time-range">
                                <input type="text" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", $break && $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                                <span>〜</span>
                                <input type="text" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", $break && $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                            </div>
                        </div>
                    @endfor

                    <div class="attendance-detail-row attendance-detail-row-note">
                        <div class="attendance-detail-label">備考</div>
                        <div class="attendance-detail-note-wrap">
                            <textarea name="reason">{{ old('reason', $displayReason) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="attendance-detail-action">
                    <button type="submit" class="attendance-detail-submit">修正</button>
                </div>
            </form>
        @endif
    </section>
@endsection
