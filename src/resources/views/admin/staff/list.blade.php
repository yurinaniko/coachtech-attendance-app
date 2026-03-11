@extends('layouts.admin')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff-list.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="section-title">スタッフ一覧</h1>
    <div class="table-wrapper">
        <table class="staff-list__table table">
            <thead>
                <tr>
                    <th class="table__col staff-list__col--name">名前</th>
                    <th class="table__col staff-list__col--email">メールアドレス</th>
                    <th class="table__col staff-list__col--action">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="table__cell staff-list__col--name">{{ $user->name }}</td>
                        <td class="table__cell staff-list__col--email">{{ $user->email }}</td>
                        <td class="table__cell staff-list__col--action">
                            <a href="{{ route('admin.staff.attendance.index', [
                            'user'  => $user->id,'month' => now()->format('Y-m')]) }}" class="staff-list__link">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection