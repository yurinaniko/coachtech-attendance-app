@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <h1 class="attendance-list__title">勤怠一覧</h1>

    {{-- 月切り替え --}}
    <div class="attendance-list__month">
        <a href="{{ route('admin.attendance.list', ['month' => $month->copy()->subMonth()->format('Y-m')]) }}">← 前月</a>
        <span class="attendance-list__month-text">
            <img src="{{ asset('images/calendar.png') }}" alt="" class="attendance-list__calendar"> {{ $month->format('Y/m') }}
        </span>
        <a href="{{ route('admin.attendance.list', ['month' => $month->copy()->addMonth()->format('Y-m')]) }}">翌月 →</a>
    </div>

    {{-- テーブル --}}
    <div class="attendance-list__table-wrapper">
        <table class="attendance-list__table">
            <thead>
                <tr>
                    <th class="attendance-list__col-date">日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    @php
                        $attendance = $user->attendances
                            ->where('work_date', $date)
                            ->first();
                    @endphp
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ optional($attendance?->clock_in_at)->format('H:i') }}</td>
                        <td>{{ optional($attendance?->clock_out_at)->format('H:i') }}</td>
                        <td>
                            {{ $attendance ? gmdate('H:i', $attendance->totalBreakMinutes() * 60) : '' }}
                        </td>
                        <td>
                            {{ $attendance ? gmdate('H:i', $attendance->totalWorkMinutes() * 60) : '' }}
                        </td>
                        <td>
                            @if ($attendance)
                                <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection