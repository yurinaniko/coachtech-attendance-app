@php
    $hideNav = Request::is('admin/login');
@endphp
<header class="header">
    <div class="header__inner">
        <div class="header__logo">
            <img src="{{ asset('images/coachtech.png') }}" class="header__logo-img" alt="COACHTECH">
        </div>
        @if (!$hideNav)
            @if(Auth::check() && Auth::user()->is_admin)
                <nav class="header__nav">
                    <ul class="header__nav-list">
                        <li>
                            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.stamp_correction_request.index') }}">申請一覧</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button class="header__logout-button">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endif
        @endif
    </div>
</header>