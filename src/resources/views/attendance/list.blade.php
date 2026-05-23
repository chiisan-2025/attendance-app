@extends('layouts.app')

@section('title','勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="attendance-list">

    <h1 class="attendance-list__title">勤怠一覧</h1>

    @if (session('message'))
        <p class="attendance-list__message">{{ session('message') }}</p>
    @endif

    <div class="attendance-list__month">
        <a href="{{ url('/attendance/list?month=' . $previousMonth) }}">← 前月</a>

        <span class="attendance-list__current">
            {{ $currentMonth->format('Y/m') }}
        </span>

        <a href="{{ url('/attendance/list?month=' . $nextMonth) }}">翌月 →</a>
    </div>

    <table class="attendance-table">
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>

        @foreach ($attendances as $attendance)
            <tr>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('m/d') }}</td>
                <td>{{ $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}</td>
                <td>{{ $attendance->total_break_time }}</td>
                <td>{{ $attendance->total_work_time }}</td>
                <td>
                    <a href="{{ url('/attendance/detail/' . $attendance->id) }}">詳細</a>
                </td>
            </tr>
        @endforeach
    </table>

</div>
@endsection