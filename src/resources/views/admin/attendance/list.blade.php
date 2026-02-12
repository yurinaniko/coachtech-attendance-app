@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">{{ $date->format('Y年n月j日') }} の勤怠一覧</h1>
        <div class="attendance-list__date">
            <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->toDateString()]) }}">
                ← 前日
            </a>
            <span class="attendance-list__date-text">
            <img src="{{ asset('images/calendar.png') }}" alt="" class="attendance-list__calendar">
            {{ $date->format('Y年n月j日') }}
            </span>
            <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->toDateString()]) }}">
                翌日 →
            </a>
        </div>
        {{-- テーブル --}}
        <div class="attendance-list__table-wrapper">
            <table class="attendance-list__table">
                <thead>
                    <tr>
                        <th class="attendance-list__col-primary">名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendances as $attendance)
                        <tr>
                            <td class="attendance-list__col-primary">{{ $attendance->user->name }}</td>
                            <td>{{ optional($attendance->clock_in_at)->format('H:i') }}</td>
                            <td>{{ optional($attendance->clock_out_at)->format('H:i') }}</td>
                            <td>{{ $attendance->break_time_hhmm }}</td>
                            <td>{{ $attendance->work_time_hhmm }}
                            </td>
                            <td>
                                <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="attendance-list__detail-link">
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