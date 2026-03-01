<header class="app-header">
    <img src="{{ asset('images/logo.png') }}" alt="COACHTECH" class="app-header__logo">

    @auth
        <nav class="app-header__nav">
            @if(auth()->user()->role === 'admin')
                {{-- 管理者メニュー --}}
                <a href="{{ route('admin.attendance.list') }}" class="app-header__link">勤怠一覧</a>
                <a href="{{ route('admin.staff.list') }}" class="app-header__link">スタッフ一覧</a>
                <a href="{{ route('stamp_correction_request.list') }}" class="app-header__link">申請</a>
            @else
                {{-- 一般ユーザーメニュー --}}
                <a href="{{ route('attendance.index') }}" class="app-header__link">勤怠</a>
                <a href="{{ route('attendance.list') }}" class="app-header__link">勤怠一覧</a>
                <a href="{{ route('stamp_correction_request.list') }}" class="app-header__link">申請</a>
            @endif

            <form method="POST" action="{{ auth()->user()->role === 'admin' ? route('admin.logout') : route('logout') }}" class="app-header__logout-form">
                @csrf
                <button type="submit" class="app-header__logout">ログアウト</button>
            </form>
        </nav>
    @endauth
</header>
