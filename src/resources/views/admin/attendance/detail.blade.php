@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
@php
    $isStatic = $isFuture || $pendingRequest;
@endphp
<div class="container">
    <h1 class="section-title">勤怠詳細</h1>
        @if ($pendingRequest)
            <div id="toast-notice" class="attendance-detail__toast">
                <div class="attendance-detail__toast-icon">
                    ⚠
                </div>
                <div class="attendance-detail__toast-content">
                    <p class="attendance-detail__toast-title">
                        修正申請があります
                    </p>
                    <p class="attendance-detail__toast-text">
                        この勤怠にはスタッフから修正申請が提出されています。
                        管理者が承認後、修正できるようになります。
                    </p>
                </div>
            </div>
        @endif
        @if (session('success'))
            <div id="toast-success" class="attendance-detail__toast attendance-detail__toast--success">
                <div class="attendance-detail__toast-icon">
                    ✓
                </div>
                <div class="attendance-detail__toast-content">
                    <p class="attendance-detail__toast-title">
                        更新しました
                    </p>
                    <p class="attendance-detail__toast-text">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
            @csrf
            @method('PUT')
            <div class="table-wrapper">
                <table class="attendance-detail__table table">
                    <tbody id="break-table">
                        <tr>
                            <th class="table__col">名前</th>
                            <td class="table__cell">
                                <span class="attendance-detail__name">{{ $attendance->user->name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th class="table__col">日付</th>
                            <td class="table__cell">
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
                            <th class="table__col">出勤・退勤</th>
                            <td class="table__cell">
                                <div class="attendance-detail__group">
                                    <div class="attendance-detail__row">
                                        @if (!$isStatic)
                                            <div class="attendance-detail__time-field">
                                                <input type="time" name="clock_in_at" class="attendance-detail__time-input"
                                                value="{{ old('clock_in_at', optional($clockIn)->format('H:i')) }}" placeholder="--:--">
                                                @error('clock_in_at')
                                                    <p class="error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <span class="attendance-detail__separator">〜</span>
                                            <div class="attendance-detail__time-field">
                                                <input type="time" name="clock_out_at" class="attendance-detail__time-input"
                                                value="{{ old('clock_out_at', optional($clockOut)->format('H:i')) }}"placeholder="--:--">
                                                @error('clock_out_at')
                                                    <p class="error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @else
                                            <div class="attendance-detail__time-field">
                                                <span class="attendance-detail__time">
                                                    {{ optional($clockIn)->format('H:i') }}
                                                </span>
                                            </div>
                                            <span class="attendance-detail__separator">〜</span>
                                            <div class="attendance-detail__time-field">
                                                <span class="attendance-detail__time">
                                                    {{ optional($clockOut)->format('H:i') }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @php
                            if (!$isStatic) {
                                $oldBreaks = old('breaks');
                                $loopCount = $oldBreaks
                                ? count($oldBreaks)
                                : $displayCount;
                            } elseif ($isFuture) {
                                $loopCount = max(1, $breaks->count());
                            } else {
                                $loopCount = $breaks->count();
                            }
                        @endphp
                        @for ($i = 0; $i < $loopCount; $i++)
                            @php
                                $oldBreaks = old('breaks', []);
                                $break = $breaks->get($i);
                                $start = $oldBreaks[$i]['break_start_at']
                                ?? $breaks->get($i)?->break_start_at?->format('H:i');
                                $end = $oldBreaks[$i]['break_end_at']
                                ?? $breaks->get($i)?->break_end_at?->format('H:i');
                            @endphp
                            <tr class="attendance-detail__break-row">
                                <th class="table__col">休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                                <td class="table__cell">
                                    <div class ="attendance-detail__group">
                                        <div class="attendance-detail__row">
                                            @if (!$isStatic)
                                                @if ($break)
                                                    <input type="hidden" name="breaks[{{ $i }}][attendance_break_id]"
                                                    value="{{ $break->id }}" placeholder="--:--">
                                                @endif
                                                <div class="attendance-detail__time-field">
                                                    <input type="time" name="breaks[{{ $i }}][break_start_at]"
                                                    class="attendance-detail__time-input" value="{{ $start ?: '' }}" placeholder="--:--">
                                                    @error("breaks.$i.break_start_at")
                                                        <p class="error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <span class="attendance-detail__separator">〜</span>
                                                <div class="attendance-detail__time-field">
                                                    <input type="time" name="breaks[{{ $i }}][break_end_at]"
                                                    class="attendance-detail__time-input" value="{{ $end ?: '' }}" placeholder="--:--">
                                                    @error("breaks.$i.break_end_at")
                                                        <p class="error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            @else
                                                <div class="attendance-detail__time-field">
                                                    <span class="attendance-detail__time">
                                                        {{ $break?->break_start_at?->format('H:i') }}
                                                    </span>
                                                </div>
                                                <span class="attendance-detail__separator">〜</span>
                                                <div class="attendance-detail__time-field">
                                                    <span class="attendance-detail__time">
                                                        {{ $break?->break_end_at?->format('H:i') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endfor
                        <template id="break-row-template">
                            <tr class="attendance-detail__break-row">
                                <th class="table__col">休憩__INDEX__</th>
                                <td class="table__cell">
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
                        <tr class="attendance-detail__note-row">
                            <th class="table__col">備考</th>
                            <td class="table__cell">
                                <div class="attendance-detail__group">
                                    <div class="attendance-detail__row">
                                        @if (!$isStatic)
                                            <textarea name="note" class="attendance-detail__note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                                        @else
                                            <div class="attendance-detail__note attendance-detail__note--text">
                                                {{ $attendance->note ?? '' }}
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
                @elseif ($isFuture)
                    <p class="attendance-detail__notice">
                        ※未来日のため修正できません。
                    </p>
                @elseif ($pendingRequest)
                    <p class="attendance-detail__notice">
                        ※承認待ちのため修正できません。
                    </p>
                @endif
            </div>
        </form>
</div>
@if(!$isStatic)
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
@endif
@if ($pendingRequest)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toast = document.getElementById('toast-notice');
    if (!toast) return;

    setTimeout(() => {
        toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-20px)';
        setTimeout(() => toast.remove(), 400);
    }, 5000);
});
</script>
@endpush
@endif
@if (session('success'))
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const success = document.getElementById('toast-success');
    if (!success) return;

    setTimeout(() => {
        success.style.transition = 'opacity 0.4s ease';
        success.style.opacity = '0';
        setTimeout(() => success.remove(), 400);
    }, 5000);
});
</script>
@endpush
@endif
@endsection