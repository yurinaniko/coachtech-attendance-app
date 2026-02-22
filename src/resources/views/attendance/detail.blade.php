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
                            <span class="attendance-detail__name">{{ $attendance->user?->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="attendance-detail__date
                            {{ $disabled ? 'attendance-detail__date--view' : 'attendance-detail__date--edit' }}">
                                <span class="attendance-detail__year">
                                    {{ $attendance->work_date?->format('Y') ?? '--' }}年
                                </span>
                                <span class="attendance-detail__month-day">
                                    {{ $attendance->work_date?->format('n') }}月{{ $attendance->work_date?->format('j') }}日
                                </span>
                            </div>
                        </td>
                    </tr>
                    @php
                        $clockIn  = $pendingRequest?->requested_clock_in_at  ?? $attendance->clock_in_at;
                        $clockOut = $pendingRequest?->requested_clock_out_at ?? $attendance->clock_out_at;
                    @endphp
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class ="attendance-detail__group">
                                <div class="attendance-detail__row">
                                    @if (!$disabled)
                                        <div class="attendance-detail__time-field">
                                            <input type="time" name="clock_in_at" class="attendance-detail__time-input"
                                            value="{{ old('clock_in_at', $clockIn?->format('H:i')) }}" placeholder="--:--">
                                            <div class="attendance-detail__error">
                                                @error('clock_in_at')
                                                    <p class="error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                        <span class="attendance-detail__separator">〜</span>
                                        <div class="attendance-detail__time-field">
                                            <input type="time" name="clock_out_at" class="attendance-detail__time-input"
                                            value="{{ old('clock_out_at', $clockOut?->format('H:i')) }}" placeholder="--:--">
                                            <div class="attendance-detail__error">
                                                @error('clock_out_at')
                                                    <p class="error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    @else
                                        <span class="attendance-detail__time">
                                            {{ $clockIn?->format('H:i') ?? '--:--' }}
                                        </span>
                                        <span class="attendance-detail__separator">〜</span>
                                        <span class="attendance-detail__time">
                                            {{ $clockOut?->format('H:i') ?? '--:--'}}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @php
                        $displayCount = $displayCount ?? 2;
                        $oldBreaks = old('breaks', []);
                        $loopCount = max($displayCount, count($oldBreaks));
                    @endphp
                        @for ($i = 0; $i < $loopCount; $i++)
                            @php
                                $break = $breaks->get($i);
                                $start = old("breaks.$i.break_start_at")
                                        ?? $break?->break_start_at?->format('H:i');
                                $end = old("breaks.$i.break_end_at")
                                        ?? $break?->break_end_at?->format('H:i');
                            @endphp
                                @if ($disabled && is_null($break))
                                    @continue
                                @endif
                            <tr class="attendance-detail__break-row">
                                <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                                <td>
                                    <div class="attendance-detail__group">
                                        <div class="attendance-detail__row">
                                            @if (!$disabled)
                                                @if ($break)
                                                    <input type="hidden" name="breaks[{{ $i }}][attendance_break_id]" value="{{ $break->id }}">
                                                @endif
                                                <div class="attendance-detail__time-field">
                                                    <input type="time" name="breaks[{{ $i }}][break_start_at]" class="attendance-detail__time-input" value="{{ $start ?? '' }}" placeholder="--:--">
                                                    <div class="attendance-detail__error">
                                                        @error("breaks.$i.break_start_at")
                                                            <p class="error">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <span class="attendance-detail__separator">〜</span>
                                                <div class="attendance-detail__time-field">
                                                    <input type="time" name="breaks[{{ $i }}][break_end_at]" class="attendance-detail__time-input" value="{{ $end ?? '' }}" placeholder="--:--">
                                                    <div class="attendance-detail__error">
                                                        @error("breaks.$i.break_end_at")
                                                            <p class="error">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                </div>
                                                @else
                                                    <span class="attendance-detail__time">
                                                        {{ $break?->break_start_at?->format('H:i') ?? '--:--' }}
                                                    </span>
                                                    <span class="attendance-detail__separator">〜</span>
                                                    <span class="attendance-detail__time">
                                                        {{ $break?->break_end_at?->format('H:i') ?? '--:--' }}
                                                    </span>
                                                @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                        <template id="break-template">
                            <tr class="attendance-detail__break-row">
                                <th>休憩__LABEL__</th>
                                <td>
                                    <div class="attendance-detail__group">
                                        <div class="attendance-detail__row">
                                            <input type="time" name="breaks[__INDEX__][break_start_at]"
                                            class="attendance-detail__time-input" placeholder="--:--">
                                            <span class="attendance-detail__separator">〜</span>
                                            <input type="time" name="breaks[__INDEX__][break_end_at]"
                                            class="attendance-detail__time-input" placeholder="--:--">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        @php
                            $note = $pendingRequest?->requested_note ?? $attendance->note;
                        @endphp
                        <tr class="attendance-detail__note-row">
                            <th>備考</th>
                            <td>
                                <div class="attendance-detail__group">
                                    <div class="attendance-detail__row">
                                        @if (!$disabled)
                                            <textarea name="note" class="form_input attendance-detail__note" rows="3">{{ old('note', $note) }}</textarea>
                                        @else
                                            <span class="attendance-detail__note-text">
                                                @if ($pendingRequest && $pendingRequest->requested_note)
                                                    {{ $pendingRequest->requested_note }}
                                                @else
                                                    {{ $attendance->note }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    @error('note')
                                        <p class="error">{{ $message }}</p>
                                    @enderror
                                </div>
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
@if (!$disabled)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.querySelector('.attendance-detail__table tbody');
    const template = document.getElementById('break-template');

    if (!tbody || !template) return;

    const refresh = (el) => {
        if (!el.value) el.classList.add('is-empty');
        else el.classList.remove('is-empty');
    };

    const attachListeners = (input) => {
        refresh(input);

        input.addEventListener('input', () => {
            refresh(input);
            checkAndAddRow();
        });

        input.addEventListener('change', () => {
            refresh(input);
            checkAndAddRow();
        });
    };

        const checkAndAddRow = () => {
            const rows = tbody.querySelectorAll('.attendance-detail__break-row');
            const lastRow = rows[rows.length - 1];
            if (!lastRow) return;
            const inputs = lastRow.querySelectorAll('input[type="time"]');
            const hasValue = Array.from(inputs).some(input => input.value);
            if (!hasValue) return;
            addNewRow(rows.length);
        };

        const addNewRow = (index) => {
            const clone = template.content.cloneNode(true);
            const th = clone.querySelector('th');
            th.textContent = `休憩${index + 1}`;
            const start = clone.querySelector('input[name*="break_start_at"]');
            const end   = clone.querySelector('input[name*="break_end_at"]');
            start.name = `breaks[${index}][break_start_at]`;
            end.name   = `breaks[${index}][break_end_at]`;
            const noteRow = document.querySelector('.attendance-detail__note-row');
            tbody.insertBefore(clone, noteRow);
            attachListeners(start);
            attachListeners(end);
        };

    document
        .querySelectorAll('.attendance-detail__time-input[type="time"]')
        .forEach(input => attachListeners(input));

    checkAndAddRow();

});
</script>
@endpush
@endif
@endsection