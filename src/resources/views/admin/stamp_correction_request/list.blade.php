@extends('layouts.app')

@section('title', '申請一覧')
@section('main_class', 'admin-request-list-page')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/stamp_correction_request/list.css') }}">

    <section class="admin-request-list-card">
        <h1 class="admin-request-list-title">申請一覧</h1>

        <div class="admin-request-list-tabs">
            <a
                href="{{ route('admin.stamp_correction_request.list', ['status' => 'pending']) }}"
                class="admin-request-list-tab {{ $status === 'pending' ? 'is-active' : '' }}"
            >
                承認待ち
            </a>
            <a
                href="{{ route('admin.stamp_correction_request.list', ['status' => 'approved']) }}"
                class="admin-request-list-tab {{ $status === 'approved' ? 'is-active' : '' }}"
            >
                承認済み
            </a>
        </div>

        <div class="admin-request-list-table-wrap">
            <table class="admin-request-list-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $item)
                        <tr>
                            <td>{{ $item->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                            <td>{{ optional($item->user)->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->target_date)->format('Y/m/d') }}</td>
                            <td>{{ $item->reason }}</td>
                            <td>{{ $item->created_at->format('Y/m/d') }}</td>
                            <td>
                                @if ($item->attendance_id)
                                    <a href="{{ route('admin.stamp_correction_request.approve', ['attendance_correct_request_id' => $item->id]) }}" class="admin-request-list-detail">詳細</a>
                                @else
                                    <span class="admin-request-list-detail">詳細</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-request-list-empty">申請データがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
