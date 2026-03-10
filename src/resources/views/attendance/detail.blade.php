@extends('layouts.app')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="section-title">勤怠詳細</h1>
    <form method="POST" action="{{ route('stamp_correction_request.store') }}">
        @csrf
            @if($attendance)
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            @endif
            @php
                $isPending = isset($pendingRequest) && $pendingRequest?->status === 'pending';
                $isStatic = $isFuture || $isPending;
            @endphp
            <div class="table-wrapper">
                <table class="attendance-detail__table table {{ $isStatic ? 'is-static' : '' }}">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>
                            <span class="attendance-detail__name">{{ $attendance?->user?->name ?? auth()->user()->name }}</span>
                        </td>
                    </tr>
                    <tr class="{{ $isStatic ? 'attendance-detail__row--date-static' : '' }}">
                        <th>日付</th>
                        <td>
                            <div class="attendance-detail__date {{ $isStatic ? 'attendance-detail__date--view' : 'attendance-detail__date--edit' }}">
                                <span class="attendance-detail__year">
                                    {{ $targetDate->format('Y') }}年
                                </span>
                                <span class="attendance-detail__month-day">
                                    {{ $targetDate->format('n') }}月{{ $targetDate->format('j') }}日
                                </span>
                            </div>
                        </td>
                    </tr>
                    @php
                        $clockIn = $pendingRequest && $pendingRequest->requested_clock_in_at !== null
                        ? $pendingRequest->requested_clock_in_at
                        : $attendance?->clock_in_at;

                        $clockOut = $pendingRequest && $pendingRequest->requested_clock_out_at !== null
                        ? $pendingRequest->requested_clock_out_at
                        : $attendance?->clock_out_at;
                    @endphp
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class ="attendance-detail__group">
                                <div class="attendance-detail__row">
                                    @if (!$isStatic)
                                        <div class="attendance-detail__time-field">
                                            <input type="time" name="clock_in_at" class="attendance-detail__time-input"
                                            value="{{ old('clock_in_at', $clockIn?->format('H:i')) }}" placeholder="--:--">
                                            @error('clock_in_at')
                                                <p class="error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <span class="attendance-detail__separator">〜</span>
                                        <div class="attendance-detail__time-field">
                                            <input type="time" name="clock_out_at" class="attendance-detail__time-input"
                                            value="{{ old('clock_out_at', $clockOut?->format('H:i')) }}" placeholder="--:--">
                                            @error('clock_out_at')
                                                <p class="error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @else
                                        <div class="attendance-detail__time-field">
                                            <span class="attendance-detail__time">
                                                {{ $clockIn?->format('H:i') }}
                                            </span>
                                        </div>
                                        <span class="attendance-detail__separator">〜</span>
                                        <div class="attendance-detail__time-field">
                                            <span class="attendance-detail__time">
                                                {{ $clockOut?->format('H:i') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @php
                        $oldBreaks = old('breaks', []);
                        $targetBreaks = $pendingRequest ? ($pendingRequest->stampCorrectionBreaks ?? collect())
                        : ($breaks ?? collect());
                        $displayCount = $isStatic ? $targetBreaks->count() : $targetBreaks->count() + 1;
                        $loopCount = max(1, $displayCount, count($oldBreaks), $targetBreaks->count());
                    @endphp
                        @for ($i = 0; $i < $loopCount; $i++)
                            @php
                                $break = $targetBreaks->get($i);
                                $start = $oldBreaks[$i]['break_start_at']
                                ?? $break?->break_start_at?->format('H:i')
                                ?? null;
                                $end = $oldBreaks[$i]['break_end_at']
                                ?? $break?->break_end_at?->format('H:i')
                                ?? null;
                            @endphp
                            <tr class="attendance-detail__break-row">
                                <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                                <td>
                                    <div class="attendance-detail__group">
                                        <div class="attendance-detail__row">
                                            @if (!$isStatic)
                                                <div class="attendance-detail__time-field">
                                                    @if ($break)
                                                        <input type="hidden" name="breaks[{{ $i }}][attendance_break_id]" value="{{ $break->id }}">
                                                    @endif
                                                    <input type="time" name="breaks[{{ $i }}][break_start_at]" class="attendance-detail__time-input" value="{{ $start ?? '' }}" placeholder="--:--">
                                                    @error("breaks.$i.break_start_at")
                                                        <p class="error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <span class="attendance-detail__separator">〜</span>
                                                <div class="attendance-detail__time-field">
                                                    <input type="time" name="breaks[{{ $i }}][break_end_at]" class="attendance-detail__time-input" value="{{ $end ?? '' }}" placeholder="--:--">
                                                    @error("breaks.$i.break_end_at")
                                                        <p class="error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @else
                                                @if ($break)
                                                    <div class="attendance-detail__time-field">
                                                        <span class="attendance-detail__time">
                                                            {{ $break->break_start_at?->format('H:i') }}
                                                        </span>
                                                    </div>
                                                    <span class="attendance-detail__separator">〜</span>
                                                    <div class="attendance-detail__time-field">
                                                        <span class="attendance-detail__time">
                                                            {{ $break->break_end_at?->format('H:i') }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <div class="attendance-detail__time-field">
                                                        <span class="attendance-detail__time">
                                                            {{ $break?->break_start_at?->format('H:i') ?? '' }}
                                                        </span>
                                                    </div>
                                                    <span class="attendance-detail__separator">〜</span>
                                                    <div class="attendance-detail__time-field">
                                                        <span class="attendance-detail__time">
                                                            {{ $break?->break_end_at?->format('H:i') ?? '' }}
                                                        </span>
                                                    </div>
                                                @endif
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
                                            <div class="attendance-detail__time-field">
                                                <input type="time" name="breaks[__INDEX__][break_start_at]"
                                                class="attendance-detail__time-input" placeholder="--:--">
                                            </div>
                                            <span class="attendance-detail__separator">〜</span>
                                            <div class="attendance-detail__time-field">
                                                <input type="time" name="breaks[__INDEX__][break_end_at]"
                                                class="attendance-detail__time-input" placeholder="--:--">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        @php
                            if ($pendingRequest) {
                                $note = $pendingRequest->requested_note;
                            } else {
                                $note = $attendance?->note ?? '';
                            }
                        @endphp
                        <tr class="attendance-detail__note-row">
                            <th>備考</th>
                            <td>
                                <div class="attendance-detail__group">
                                    <div class="attendance-detail__row">
                                        <!-- 未来日または承認待ちの時-->
                                        @if (!$isStatic)
                                            <textarea name="note" class="form_input attendance-detail__note" rows="3">{{ old('note', $note) }}</textarea>
                                        @else
                                            <div class="attendance-detail__note-text">
                                                {{ $note }}
                                            </div>
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
            @if (!$isStatic)
                <button type="submit" class="attendance-detail__edit-btn">
                    修正
                </button>
                @if(session('error'))
                    <p class="error">
                        {{ session('error') }}
                    </p>
                @endif
            @else
                <p class="attendance-detail__notice">
                    {{ $notice }}
                </p>
            @endif
        </div>
    </form>
</div>
@if($attendance && !$isStatic)
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