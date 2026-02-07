@extends('layouts.app')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
    <form method="POST" action="{{ route('stamp_correction_requests.store') }}">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
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
                                <input type="time" name="clock_in_at" class="attendance-detail__time-input"
                                value="{{ optional($attendance->clock_in_at)->format('H:i') }}">
                                <span>〜</span>
                                <input type="time" name="clock_out_at" class="attendance-detail__time-input"
                                value="{{ optional($attendance->clock_out_at)->format('H:i') }}">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            @php $break = $attendance->breaks->get(0); @endphp
                                <div class="attendance-detail__time-group">
                                {{-- 休憩1 --}}
                                    @if ($break)
                                        <input type="hidden" name="breaks[0][attendance_break_id]" value="{{ $break->id }}" class="attendance-detail__time-input">
                                    @endif
                                    <input type="time" name="breaks[0][break_start_at]" value="{{ $break?->break_start_at?->format('H:i') }}"class="attendance-detail__time-input">
                                    <span>〜</span>
                                    <input type="time" name="breaks[0][break_end_at]" value="{{ $break?->break_end_at?->format('H:i') }}"class="attendance-detail__time-input">
                                </div>
                        </td>
                    </tr>
                    <tr>
                        <th>休憩2</th>
                        <td>
                            @php $break = $attendance->breaks->get(1); @endphp
                                <div class="attendance-detail__time-group">
                                    @if ($break)
                                        {{-- 休憩2 --}}
                                        <input type="hidden"  name="breaks[1][attendance_break_id]"
                                        value="{{ $break->id }}" class="attendance-detail__time-input">
                                    @endif
                                    <input type="time" name="breaks[1][break_start_at]"
                                    value="{{ $break?->break_start_at?->format('H:i') }}"class="attendance-detail__time-input">
                                    <span>〜</span>
                                    <input type="time" name="breaks[1][break_end_at]"
                                    value="{{ $break?->break_end_at?->format('H:i') }}"class="attendance-detail__time-input">
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
                <button type="submit" class="attendance-detail__edit-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection