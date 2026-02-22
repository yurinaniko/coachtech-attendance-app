@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
@php
    $disabled = $pendingRequest !== null;
@endphp
<div class="attendance-detail">
    <h1 class="attendance-detail__title">勤怠詳細</h1>
        @if (session('success'))
            <div class="success-message">
                {{ session('success') }}
            </div>
        @endif
    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')
        <div class="attendance-detail__wrapper">
            <table class="attendance-detail__table">
                <tbody id="break-table">
                    <tr>
                        <th>名前</th>
                        <td>
                            <span class="attendance-detail__name">{{ $attendance->user->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="attendance-detail__date attendance-detail__date--edit">
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
                            <div class ="attendance-detail__group">
                                <div class="attendance-detail__row">
                                    <div class="attendance-detail__time-field">
                                        <input type="time" name="clock_in_at" class="attendance-detail__time-input"
                                        value="{{ old('clock_in_at', optional($clockIn)->format('H:i')) }}" placeholder="--:--">
                                        <div class="attendance-detail__error">
                                            @error('clock_in_at')
                                                <p class="error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                    <span class="attendance-detail__separator">〜</span>
                                    <div class="attendance-detail__time-field">
                                        <input type="time" name="clock_out_at" class="attendance-detail__time-input"
                                        value="{{ old('clock_out_at', optional($clockOut)->format('H:i')) }}"placeholder="--:--">
                                        @error('clock_out_at')
                                            <p class="error">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                        @for ($i = 0; $i < $displayCount; $i++)
                            @php
                                $break = $breaks->get($i);
                                $start = old(
                                    "breaks.$i.break_start_at",
                                    $break?->break_start_at?->format('H:i')
                                );
                                $end = old(
                                    "breaks.$i.break_end_at",
                                    $break?->break_end_at?->format('H:i')
                                );
                            @endphp
                            <tr class="attendance-detail__break-row">
                                <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                                <td>
                                    <div class ="attendance-detail__group">
                                        <div class="attendance-detail__row">
                                            @if ($break)
                                                <input type="hidden" name="breaks[{{ $i }}][attendance_break_id]"
                                                value="{{ $break->id }}" placeholder="--:--">
                                            @endif
                                            <div class="attendance-detail__time-field">
                                                <input type="time" name="breaks[{{ $i }}][break_start_at]"
                                                class="attendance-detail__time-input" value="{{ $start ?: '' }}" placeholder="--:--">
                                                <div class="attendance-detail__error">
                                                    @error("breaks.$i.break_start_at")
                                                        <p class="error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                            <span class="attendance-detail__separator">〜</span>
                                            <div class="attendance-detail__time-field">
                                                <input type="time" name="breaks[{{ $i }}][break_end_at]"
                                                class="attendance-detail__time-input" value="{{ $end ?: '' }}" placeholder="--:--">
                                                <div class="attendance-detail__error">
                                                    @error("breaks.$i.break_end_at")
                                                        <p class="error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                        <template id="break-row-template">
                            <tr class="attendance-detail__break-row">
                                <th>休憩__INDEX__</th>
                                <td>
                                    <div class="attendance-detail__group">
                                        <div class="attendance-detail__row">

                                            <input type="time"
                                            name="breaks[__INDEX__][break_start_at]"
                                            class="attendance-detail__time-input" placeholder="--:--">

                                            <span class="attendance-detail__separator">〜</span>

                                            <input type="time"
                                            name="breaks[__INDEX__][break_end_at]"
                                            class="attendance-detail__time-input" placeholder="--:--">

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr class="attendance-detail__note-row">
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
                <button type="submit" class="attendance-detail__edit-btn">
                    修正
                </button>
            @endif
        </div>
    </form>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    const tbody = document.getElementById('break-table');
    const template = document.getElementById('break-row-template');

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

@endsection