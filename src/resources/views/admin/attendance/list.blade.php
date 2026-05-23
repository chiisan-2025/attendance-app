@extends('layouts.admin')

@section('title','勤怠一覧')

@section('content')

    <h1 class="page-title">{{ $currentDate->format('Y年n月j日') }}の勤怠</h1>

    <div class="date-nav">
        <a href="{{ url('/admin/attendance/list?date=' . $previousDate) }}">← 前日</a>
        <span class="date-center">{{ $currentDate->format('Y/m/d') }}</span>
        <a href="{{ url('/admin/attendance/list?date=' . $nextDate) }}">翌日 →</a>
    </div>

    <table class="card-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}</td>
                    <td>{{ $attendance->total_break_time }}</td>
                    <td>{{ $attendance->total_work_time }}</td>
                    <td>
                        <a class="link-detail" href="{{ url('/admin/attendance/' . $attendance->id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection