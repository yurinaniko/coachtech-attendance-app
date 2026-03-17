@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="section-title">{{ $date->format('Y年n月j日') }} の勤怠一覧</h1>
    <div class="attendance-list__date">
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->toDateString()]) }}" class="attendance-list__link">
            ← 前日
        </a>
        <span class="attendance-list__text">
            <img src="{{ asset('images/calendar.png') }}" alt="" class="attendance-list__calendar">
                {{ $date->format('Y年n月j日') }}
        </span>
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->toDateString()]) }}" class="attendance-list__link">
            翌日 →
        </a>
    </div>
    <div class="table-wrapper">
        <table class="attendance-list__table table">
            <thead>
                <tr>
                    <th class="attendance-list__col--name">名前</th>
                    <th class="table__col">出勤</th>
                    <th class="table__col">退勤</th>
                    <th class="table__col">休憩</th>
                    <th class="table__col">合計</th>
                    <th class="table__col attendance-list__col--detail">詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                    <tr>
                        <td class="attendance-list__col--name">{{ $attendance->user->name }}</td>
                        <td class="table__cell">{{ optional($attendance->clock_in_at)->format('H:i') }}</td>
                        <td class="table__cell">{{ optional($attendance->clock_out_at)->format('H:i') }}</td>
                        <td class="table__cell">{{ $attendance->break_time_hhmm }}</td>
                        <td class="table__cell">{{ $attendance->work_time_hhmm }}</td>
                        <td class="table__cell attendance-list__col--detail">
                            <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="attendance-list__link--detail">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection