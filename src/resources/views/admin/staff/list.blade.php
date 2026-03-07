@extends('layouts.app')

@section('title', 'スタッフ一覧')
@section('main_class', 'admin-staff-list-page')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/admin/staff/list.css') }}">

    <section class="admin-staff-list-card">
        <h1 class="admin-staff-list-title">スタッフ一覧</h1>

        <div class="admin-staff-table-wrap">
            <table class="admin-staff-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>メールアドレス</th>
                        <th>月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffs as $staff)
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>
                                <a href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}" class="admin-staff-detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="admin-staff-empty">スタッフデータがありません。</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
