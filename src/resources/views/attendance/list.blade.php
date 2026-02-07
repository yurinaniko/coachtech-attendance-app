@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>

    {{-- 月切り替え --}}
    <div class="attendance-list__date">
        <a href="{{ route('attendance.list', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">← 前月</a>
        <span class="attendance-list__date-text">
            <img src="{{ asset('images/calendar.png') }}" alt="" class="attendance-list__calendar"> {{ $month->format('Y/m') }}
        </span>
        <a href="{{ route('attendance.list', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">翌月 →</a>
    </div>

    {{-- テーブル --}}
    <div class="attendance-list__table-wrapper">
        <table class="attendance-list__table">
            <thead>
                <tr>
                    <th class="attendance-list__col-primary">日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
            @for ($day = 1; $day <= $month->daysInMonth; $day++)
                @php
                    $date = $month->copy()->day($day);
                    $dateKey = $date->format('Y-m-d');
                    $attendance = $attendances->get($dateKey);
                @endphp
                <tr>
                    <td class="attendance-list__col-primary">
                        <span class="attendance-list__date-main">
                            {{ $date->format('m/d') }}
                        </span>
                        <span class="attendance-list__date-week">
                            （{{ $date->isoFormat('dd') }}）
                        </span>
                    </td>
                    <td>
                        {{ optional($attendance)->clock_in_at?->format('H:i') }}
                    </td>
                    <td>
                        {{ optional($attendance)->clock_out_at?->format('H:i') }}
                    </td>
                    <td>
                        @if ($attendance && $attendance->break_seconds > 0)
                            {{ $attendance->break_time_hhmm }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance && $attendance->work_seconds > 0)
                            {{ $attendance->work_time_hhmm }}
                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                            <a href="{{ route('attendance.detail', $attendance->id) }}" class="attendance-list__detail">
                                詳細
                            </a>
                        @endif
                    </td>
                </tr>
            @endfor
            </tbody>
        </table>
    </div>
</div>
@endsection