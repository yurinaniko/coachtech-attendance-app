@extends('layouts.app')

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
                                <input type="text" name="clock_in_at" class="attendance-detail__time-input"
                                value="{{ optional($attendance->clock_in_at)->format('H:i') }}" placeholder="00:00">
                                <span>〜</span>
                                <input type="text" name="clock_out_at" class="attendance-detail__time-input"
                                value="{{ optional($attendance->clock_out_at)->format('H:i') }}" placeholder="00:00">
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
                                <input type=text class="attendance-detail__time-input" value="{{ $break1Start ?? '' }}"
                                {{ $break1Start ? '' : 'readonly' }}>
                                <span>〜</span>
                                <input type=text class="attendance-detail__time-input" value="{{ $break1End ?? '' }}"
                                {{ $break1End ? '' : 'readonly' }}>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            <div class="attendance-detail__time-group">
                                <input type="text"class="attendance-detail__time-input" value="{{ $break2Start ?? '' }}"
                                {{ $break2Start ? '' : 'readonly' }}>
                                <span>〜</span>
                                <input type="text" class="attendance-detail__time-input" value="{{ $break2End ?? '' }}"
                                {{ $break2End ? '' : 'readonly' }}>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="note" class="form_input attendance-detail__note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="attendance-detail__actions">
            @php
                $latestRequest = $attendance->stampCorrectionRequests->last();
            @endphp

            @if ($latestRequest && $latestRequest->status === 'pending')
                <p class="attendance-detail__notice">
                    ※承認待ちのため修正できません。
                </p>
            @else
                <form method="POST" action="{{ route('stamp_correction_requests.store') }}">
                    @csrf
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                    <button type="submit" class="attendance-detail__edit-btn">修正</button>
                </form>
            @endif
        </div>
</div>
@endsection