@extends('layouts.app')

@section('title', '勤怠詳細')
@section('main_class', 'admin-approve-page')

@php
    $user = $correctionRequest->user;
    $targetDate = \Carbon\Carbon::parse($correctionRequest->target_date);
    $breaks = $correctionRequest->breaks->values();
@endphp

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/approve.css') }}">

    <section class="admin-approve-card">
        <h1 class="admin-approve-title">勤怠詳細</h1>

        <div class="admin-approve-panel">
            <div class="admin-approve-row">
                <div class="admin-approve-label">名前</div>
                <div class="admin-approve-value">{{ optional($user)->name }}</div>
            </div>

            <div class="admin-approve-row">
                <div class="admin-approve-label">日付</div>
                <div class="admin-approve-date">
                    <span>{{ $targetDate->format('Y年') }}</span>
                    <span>{{ $targetDate->format('n月j日') }}</span>
                </div>
            </div>

            <div class="admin-approve-row">
                <div class="admin-approve-label">出勤・退勤</div>
                <div class="admin-approve-time-range">
                    <span>{{ $correctionRequest->requested_clock_in ? \Carbon\Carbon::parse($correctionRequest->requested_clock_in)->format('H:i') : '' }}</span>
                    <span>〜</span>
                    <span>{{ $correctionRequest->requested_clock_out ? \Carbon\Carbon::parse($correctionRequest->requested_clock_out)->format('H:i') : '' }}</span>
                </div>
            </div>

            @forelse ($breaks as $i => $break)
                <div class="admin-approve-row">
                    <div class="admin-approve-label">{{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}</div>
                    <div class="admin-approve-time-range">
                        <span>{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</span>
                        <span>〜</span>
                        <span>{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</span>
                    </div>
                </div>
            @empty
                <div class="admin-approve-row">
                    <div class="admin-approve-label">休憩</div>
                    <div class="admin-approve-time-range">
                        <span></span>
                        <span>〜</span>
                        <span></span>
                    </div>
                </div>
            @endforelse

            <div class="admin-approve-row admin-approve-row-note">
                <div class="admin-approve-label">備考</div>
                <div class="admin-approve-value admin-approve-value--note">{{ $correctionRequest->reason }}</div>
            </div>
        </div>

        <form
            action="{{ route('admin.stamp_correction_request.approve.update', ['attendance_correct_request_id' => $correctionRequest->id]) }}"
            method="POST"
            class="admin-approve-action"
        >
            @csrf
            @if ($correctionRequest->status === 'approved')
                <button type="button" class="admin-approve-submit admin-approve-submit--done" disabled>承認済み</button>
            @else
                <button type="submit" class="admin-approve-submit">承認</button>
            @endif
        </form>
    </section>
@endsection
