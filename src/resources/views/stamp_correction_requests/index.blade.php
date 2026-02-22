@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp-request.css') }}">
@endsection

@section('content')
<div class="stamp-request-index">
    <h1 class="stamp-request-index__title">申請一覧</h1>
        <div class="stamp-request-index__tabs">
            <a href="{{ route('stamp_correction_requests.index', ['status' => 'pending']) }}"
            class="stamp-request-index__tab {{ request('status', 'pending') === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('stamp_correction_requests.index', ['status' => 'approved']) }}"
            class="stamp-request-index__tab {{ request('status') === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>
        <div class="stamp-request-index__table-wrapper">
            <table class="stamp-request-index__table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td>
                                <span class="stamp-request-index__status stamp-request-index__status--{{ $request->status }}">
                                {{ $request->status_label }}
                                </span>
                            </td>
                            <td>{{ $request->user->name }}</td>
                            <td>@if ($request->requested_clock_in_at && $request->requested_clock_out_at)
                                    {{ $request->requested_clock_in_at->format('Y/m/d') }}<br>
                                @else
                                    -
                                @endif
                            <td>{{ $request->requested_note }}</td>
                            <td>{{ optional($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('attendance.detailByDate', $request->attendance->work_date->format('Y-m-d')) }}"  class="stamp-request-index__detail">
                                    詳細
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="stamp-request-index__empty">
                            申請はありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
</div>
@endsection