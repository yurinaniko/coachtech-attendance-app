@php
    $hideNav =
        Request::is('login*') ||
        Request::is('register*') ||
        Request::is('email/*');
@endphp
<header class="header">
    <div class="header__inner">
        <div class="header__logo">
            <img src="{{ asset('images/coachtech.png') }}" class="header__logo-img" alt="COACHTECH">
        </div>
        @if (!$hideNav)
            @auth
                <nav class="header__nav">
                    <ul class="header__nav-list">
                        <li>
                            <a href="{{ route('attendance.index') }}">勤怠</a>
                        </li>
                        <li>
                            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('stamp_correction_requests.index') }}">申請</a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="header__logout-button">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endauth
        @endif
    </div>
</header>