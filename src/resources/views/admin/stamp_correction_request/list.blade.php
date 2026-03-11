@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp-request.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="section-title">申請一覧</h1>
    <div class="stamp-request-index__tabs">
        <a href="{{ route('admin.stamp_correction_request.index', ['status' => 'pending']) }}"
        class="stamp-request-index__tab {{ request('status', 'pending') === 'pending' ? 'is-active' : '' }}">承認待ち</a>
        <a href="{{ route('admin.stamp_correction_request.index', ['status' => 'approved']) }}"
        class="stamp-request-index__tab {{ request('status') === 'approved' ? 'is-active' : '' }}">承認済み</a>
    </div>
    <div class="table-wrapper">
        <table class="stamp-request-index__table table">
            <thead>
                <tr>
                    <th class="table__col">状態</th>
                    <th class="table__col">名前</th>
                    <th class="table__col">対象日時</th>
                    <th class="table__col">申請理由</th>
                    <th class="table__col">申請日時</th>
                    <th class="table__col">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $stampRequest)
                    <tr>
                        <td class="table__cell">
                            <span class="stamp-request-index__status">
                                {{ $stampRequest->status_label }}
                            </span>
                        </td>
                        <td class="table__cell">{{ $stampRequest->user->name }}</td>
                        <td class="table__cell stamp-request-index__date">{{ optional($stampRequest->attendance?->work_date)->format('Y/m/d') ?? '-' }}</td>
                        <td class="table__cell stamp-request-index__reason">{{ $stampRequest->requested_note }}</td>
                        <td class="table__cell stamp-request-index__date">{{ $stampRequest->created_at->format('Y/m/d') }}</td>
                        <td class="table__cell">
                            <a href="{{ route('admin.stamp_correction_request.edit', $stampRequest->id) }}" class="stamp-request-index__detail">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="table__cell stamp-request-index__empty">
                            申請はありません
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection