@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
        <div class="attendance-detail__wrapper">
            <table class="attendance-detail__table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>
                            <span class="attendance-detail__name">{{ $attendance->user->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="attendance-detail__date">
                                <span class="attendance-detail__year">
                                    {{ $attendance->work_date->format('Y') }}年
                                </span>
                                <span class="attendance-detail__month-day">
                                    {{ $attendance->work_date->format('n') }}月{{ $attendance->work_date->format('j') }}日
                                </span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="attendance-detail__time-group">
                                {{ optional($attendance->clock_in_at)->format('H:i') ?? '--:--' }}
                                〜
                                {{ optional($attendance->clock_out_at)->format('H:i') ?? '--:--' }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        @php
                            $break1 = $attendance->breaks->get(0);
                            $break2 = $attendance->breaks->get(1);
                            $break1Start = optional($break1?->break_start_at)->format('H:i');
                            $break1End   = optional($break1?->break_end_at)->format('H:i');
                            $break2Start = optional($break2?->break_start_at)->format('H:i');
                            $break2End   = optional($break2?->break_end_at)->format('H:i');
                        @endphp
                        <th>休憩</th>
                        <td>
                            <div class="attendance-detail__time-group">
                                {{ $break1Start ?? '--:--' }} 〜 {{ $break1End ?? '--:--' }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            <div class="attendance-detail__time-group">
                                {{ $break2Start ?? '--:--' }} 〜 {{ $break2End ?? '--:--' }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            {{ $attendance->note ?? '—' }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="attendance-detail__actions">
            <form method="POST" action="{{ route('admin.stamp_correction_requests.approve', $request->id) }}">
                @csrf
            </form>
            <form method="POST" action="{{ route('stamp_correction_requests.store') }}">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <button type="submit">修正申請</button>
            </form>
        </div>
</div>
@endsection