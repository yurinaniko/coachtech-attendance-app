@extends('layouts.app')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
@php
    $disabled = $attendance->hasPendingRequest();
@endphp
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
                                value="{{ old('clock_in_at', optional($attendance->clock_in_at)->format('H:i')) }}" {{ $disabled ? 'disabled' : '' }}>
                                @error('clock_in_at')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                                <span>〜</span>
                                <input type="time" name="clock_out_at" class="attendance-detail__time-input"
                                value="{{ old('clock_out_at', optional($attendance->clock_out_at)->format('H:i')) }}">
                                @error('clock_out_at')
                                    <p class="error">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @for ($i = 0; $i < $displayCount; $i++)
                        @php
                            $break = $breaks->get($i);
                            $start = old("breaks.$i.break_start_at",optional($break?->break_start_at)->format('H:i'));
                            $end   = old("breaks.$i.break_end_at",optional($break?->break_end_at)->format('H:i'));
                        @endphp
                        <tr>
                            <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                            <td>
                                <div class="attendance-detail__time-group">
                                    @if ($break)
                                        <input type="hidden" name="breaks[{{ $i }}][attendance_break_id]"
                                        value="{{ $break->id }}">
                                    @endif
                                    <input type="time" name="breaks[{{ $i }}][break_start_at]" class="attendance-detail__time-input"
                                    value="{{ $start }}">
                                    @error("breaks.$i.break_start_at")
                                        <p class="error">{{ $message }}</p>
                                    @enderror
                                    <span>〜</span>
                                    {{-- 終了 --}}
                                    <input type="time" name="breaks[{{ $i }}][break_end_at]"
                                    class="attendance-detail__time-input"
                                    value="{{ $end }}">
                                    @error("breaks.$i.break_end_at")
                                        <p class="error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </td>
                        </tr>
                    @endfor
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="note" class="form_input attendance-detail__note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                            @error('note')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="attendance-detail__actions">
            @if ($disabled)
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