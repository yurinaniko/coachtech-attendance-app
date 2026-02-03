@extends('layouts.app')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection
@section('content')
<div class="attendance-wrapper">
    <div class="attendance attendance__{{ $status }}">
    {{-- ステータス表示 --}}
        <p class="attendance__status">
            @if ($status === 'before_work')
                勤務外
            @elseif ($status === 'working')
                出勤中
            @elseif ($status === 'on_break')
                休憩中
            @elseif ($status === 'after_work')
                退勤済
            @endif
        </p>
        {{-- 日付 --}}
        <p class="attendance__date">
            {{ now()->isoFormat('Y年M月D日（ddd）') }}
        </p>
        {{-- 現在時刻 --}}
        <p class="attendance__time">
            {{ now()->format('H:i') }}
        </p>
        {{-- 出勤前 --}}
        @if ($status === 'before_work')
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button type="submit" class="attendance__button attendance__button--primary">
                    出勤
                </button>
            </form>
            {{-- 出勤中 --}}
        @elseif ($status === 'working')
            <div class="attendance__buttons">
                <form method="POST" action="{{ route('attendance.clockOut') }}">
                    @csrf
                    <button class="attendance__button attendance__button--primary">
                        退勤
                    </button>
                </form>
                <form method="POST" action="{{ route('attendance.breakStart') }}">
                    @csrf
                    <button class="attendance__button attendance__button--sub">
                        休憩入
                    </button>
                </form>
            </div>

            {{-- 休憩中 --}}
        @elseif ($status === 'on_break')
            <form method="POST" action="{{ route('attendance.breakEnd') }}">
                @csrf
                <button class="attendance__button attendance__button--primary">
                    休憩戻
                </button>
            </form>

            {{-- 退勤後 --}}
        @elseif ($status === 'after_work')
            <p class="attendance__message">
                お疲れ様でした。
            </p>
        @endif
    </div>
</div>
@endsection