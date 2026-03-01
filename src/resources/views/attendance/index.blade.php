@extends('layouts.app')

@section('title', '勤怠')
@section('main_class', 'attendance-page')

@php
    $now = now();
    $week = ['日', '月', '火', '水', '木', '金', '土'];
    $jpDate = $now->format('Y年n月j日') . '(' . $week[$now->dayOfWeek] . ')';
@endphp

@section('content')
    <section class="attendance-card">
        <p class="attendance-status">{{ $status }}</p>
        <p class="attendance-date">{{ $jpDate }}</p>
        <p class="attendance-time">{{ $now->format('H:i') }}</p>
        @if ($status === '勤務外')
            <form method="POST" action="{{ route('attendance.clock_in') }}">
                @csrf
                <button type="submit" class="attendance-clockin">出勤</button>
            </form>
        @elseif ($status === '出勤中')
            <div class="attendance-actions">
                <form method="POST" action="{{ route('attendance.clock_out') }}">
                    @csrf
                    <button type="submit" class="attendance-action attendance-action--primary">退勤</button>
                </form>

                <form method="POST" action="{{ route('attendance.break_in') }}">
                    @csrf
                    <button type="submit" class="attendance-action attendance-action--secondary">休憩入</button>
                </form>
            </div>
        @elseif ($status === '休憩中')
            <form method="POST" action="{{ route('attendance.break_out') }}">
                @csrf
                <button type="submit" class="attendance-clockin">休憩戻</button>
            </form>
        @else
            <p class="attendance-finished-message">お疲れ様でした。</p>
        @endif
    </section>
@endsection
