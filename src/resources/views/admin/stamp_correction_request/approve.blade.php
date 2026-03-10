@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href= "{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="section-title">勤怠詳細</h1>
    <div class="table-wrapper">
        <table class="attendance-detail__table table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="attendance-detail__name">
                            {{ $request->attendance->user->name }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="attendance-detail__date attendance-detail__date--view">
                            <span class="attendance-detail__year">{{ $request->attendance->work_date->format('Y') }}年</span>
                            <span class="attendance-detail__month-day">{{ $request->attendance->work_date->format('n') }}月{{ $request->attendance->work_date->format('j') }}日</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="attendance-detail__group">
                            <div class="attendance-detail__row">
                                <div class="attendance-detail__time-field">
                                    <span class="attendance-detail__time">{{ optional($request->requested_clock_in_at)->format('H:i') ?? '--:--' }}</span>
                                </div>
                                <span class="attendance-detail__separator">〜</span>
                                <div class="attendance-detail__time-field">
                                    <span class="attendance-detail__time">{{ optional($request->requested_clock_out_at)->format('H:i') ?? '--:--' }}</span>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @php
                    $breaks = $request->stampCorrectionBreaks->isNotEmpty()
                    ? $request->stampCorrectionBreaks
                    : $request->attendance->breaks;
                    $loopCount = $breaks->count() + 1;
                @endphp
                @for ($i = 0; $i < $loopCount; $i++)
                    @php
                        $break = $breaks->get($i);
                    @endphp
                    <tr class="attendance-detail__break-row">
                        <th>休憩{{ $i === 0 ? '' : $i + 1 }}</th>
                        <td>
                            <div class="attendance-detail__group">
                                <div class="attendance-detail__row">
                                    <div class="attendance-detail__time-field">
                                        <span class="attendance-detail__time">{{ optional($break?->break_start_at)->format('H:i') }}</span>
                                    </div>
                                    <span class="attendance-detail__separator">〜</span>
                                    <div class="attendance-detail__time-field">
                                        <span class="attendance-detail__time">{{ optional($break?->break_end_at)->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endfor
                <tr class="attendance-detail__note-row">
                    <th>備考</th>
                    <td>
                        <div class="attendance-detail__group">
                            <div class="attendance-detail__row">
                                <div class="attendance-detail__note attendance-detail__note-text">
                                    {{ $request->requested_note ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="attendance-detail__actions">
        @if ($request->status === 'pending')
            <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}">
                @csrf
                <button type="submit" class="attendance-detail__approve-button">承認</button>
            </form>
        @else
            <button class="attendance-detail__approved-button" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection