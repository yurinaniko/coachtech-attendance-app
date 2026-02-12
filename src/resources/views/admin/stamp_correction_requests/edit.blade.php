@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp-request-detail.css') }}">
@endsection

@section('content')
<div class="stamp-request-detail">
    <h1 class="stamp-request-detail__title">勤怠詳細</h1>

    <div class="stamp-request-detail__wrapper">
        <table class="stamp-request-detail__table">
            <tbody>
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="stamp-request-detail__row-value stamp-request-detail__row-value--name">
                            {{ $request->attendance->user->name }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>
                        <div class="stamp-request-detail__row-value stamp-request-detail__row-value--date">
                            <span>{{ $request->attendance->work_date->format('Y') }}年</span>
                            <span>{{ $request->attendance->work_date->format('n') }}月{{ $request->attendance->work_date->format('j') }}日</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="stamp-request-detail__row-value stamp-request-detail__row-value--time">
                            <span>{{ optional($request->attendance->clock_in_at)->format('H:i') ?? '--:--' }}</span>
                            <span>〜</span>
                            <span>{{ optional($request->attendance->clock_out_at)->format('H:i') ?? '--:--' }}</span>
                        </div>
                    </td>
                </tr>
                    @php
                        $break1 = $request->attendance->breaks->get(0);
                        $break2 = $request->attendance->breaks->get(1);
                        $break1Start = optional($break1?->break_start_at)->format('H:i');
                        $break1End   = optional($break1?->break_end_at)->format('H:i');
                        $break2Start = optional($break2?->break_start_at)->format('H:i');
                        $break2End   = optional($break2?->break_end_at)->format('H:i');
                    @endphp
                <tr>
                    <th>休憩</th>
                    <td>
                        <div class="stamp-request-detail__row-value stamp-request-detail__row-value--time">
                            <span>{{ optional($break1?->break_start_at)->format('H:i') ?? '--:--' }}</span>
                            <span>〜</span>
                            <span>{{ optional($break1?->break_end_at)->format('H:i') ?? '--:--' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>休憩2</th>
                    <td>
                        <div class="stamp-request-detail__row-value stamp-request-detail__row-value--time">
                            <span>{{ optional($break2?->break_start_at)->format('H:i') ?? '--:--' }}</span>
                            <span>〜</span>
                            <span>{{ optional($break2?->break_end_at)->format('H:i') ?? '--:--' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>
                        <span class="stamp-request-detail__row-value stamp-request-detail__row-value--note">
                            {{ $request->requested_note ?? '—' }}
                        </span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="stamp-request-detail__actions">
        @if ($request->status === 'pending')
            <form method="POST" action="{{ route('admin.stamp_correction_requests.approve', $request->id) }}">
                @csrf
                <button type="submit" class="stamp-request-detail__approve-button">承認</button>
            </form>
        @else
            <button class="stamp-request-detail__approved-button" disabled>承認済み</button>
        @endif
    </div>
</div>
@endsection