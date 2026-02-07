@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
<div class="request-index">
    <h1 class="request-index__title">申請一覧</h1>
        <div class="request-index__tabs">
            <a href="{{ route('stamp_correction_requests.index', ['status' => 'pending']) }}"
            class="request-index__tab {{ request('status', 'pending') === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('stamp_correction_requests.index', ['status' => 'approved']) }}"
            class="request-index__tab {{ request('status') === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>
        <div class="request-index__table-wrapper">
            <table class="request-index__table">
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
                                <span class="request-index__status request-index__status--{{ $request->status }}">
                                {{ $request->status_label }}
                                </span>
                            </td>
                            <td>{{ $request->user->name }}</td>
                            <td>{{ optional($request->target_date)->format('Y/m/d') ?? '-' }}</td>
                            <td>{{ $request->reason }}</td>
                            <td>{{ optional($request->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="{{ route('stamp_correction_requests.show', $request->id) }}"class="request-index__detail">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="request-index__empty">
                            申請はありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
</div>
@endsection