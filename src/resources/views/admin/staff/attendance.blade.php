@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="section-title">{{ $user->name }}さんの勤怠一覧</h1>
    <div class="attendance-list__date">
        <a href="{{ route('admin.staff.attendance.index', ['user' => $user->id,'month' => $month->copy()->subMonth()->format('Y-m')]) }}" class="attendance-list__link">← 前月</a>
        <span class="attendance-list__text">
            <img src="{{ asset('images/calendar.png') }}" alt="" class="attendance-list__calendar"> {{ $month->format('Y/m') }}
        </span>
        <a href="{{ route('admin.staff.attendance.index', ['user' => $user->id,'month' => $month->copy()->addMonth()->format('Y-m')]) }}" class="attendance-list__link">翌月 →</a>
    </div>
    <div class="table-wrapper">
        <table class="attendance-list__table table">
            <thead>
                <tr>
                    <th class="table__col attendance-list__col--date">日付</th>
                    <th class="table__col">出勤</th>
                    <th class="table__col">退勤</th>
                    <th class="table__col">休憩</th>
                    <th class="table__col">合計</th>
                    <th class="table__col">詳細</th>
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
                        <td class="table__cell attendance-list__col--date">
                            {{ $date->format('m/d') }}（{{ $date->isoFormat('dd') }}）
                        </td>
                        <td class="table__cell">
                            {{ optional($attendance?->clock_in_at)->format('H:i') ?? '' }}
                        </td>
                        <td class="table__cell">
                            {{ optional($attendance?->clock_out_at)->format('H:i') ?? '' }}
                        </td>
                        <td class="table__cell">
                            @if ($attendance && $attendance->break_seconds > 0)
                                {{ $attendance->break_time_hhmm }}
                            @endif
                        </td>
                        <td class="table__cell">
                            @if ($attendance && $attendance->work_seconds > 0)
                                {{ $attendance->work_time_hhmm }}
                            @endif
                        </td>
                        <td class="table__cell">
                            @if ($attendance)
                                <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="attendance-list__detail">詳細</a>
                            @else
                                <span class="attendance-list__detail--disabled">詳細</span>
                            @endif
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="attendance-list__actions">
        <a href="{{ route('admin.staff.attendance.csv', ['user' => $user->id,'month' => $month->format('Y-m')]) }}"class="attendance-list__csv-btn">
            CSV出力
        </a>
    </div>
</div>
@endsection