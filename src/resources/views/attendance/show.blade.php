@extends('layouts.app')

@section('title','勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
    <h1 class="page-title">勤怠詳細</h1>

    <form method="POST" action="{{ url('/attendance/detail/' . $attendance->id . '/request') }}">
        @csrf

        <div class="detail-card">

    <div class="detail-row">
        <div class="detail-label">名前</div>
        <div class="detail-value">{{ $attendance->user->name }}</div>
    </div>

    <div class="detail-row">
        <div class="detail-label">日付</div>
        <div class="detail-value detail-date">
            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
            <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
        </div>
    </div>

    <div class="detail-row">
        <div class="detail-label">出勤・退勤</div>
        <div class="detail-value detail-time-range">
            <input class="time-box"
                    type="time"
                    name="requested_clock_in_at"
                    value="{{ old('requested_clock_in_at', \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i')) }}">
            <span class="time-separator">〜</span>

            <input class="time-box"
                    type="time"
                    name="requested_clock_out_at"
                    value="{{ old('requested_clock_out_at', \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i')) }}">
        </div>
    </div>
    @error('requested_clock_in_at')
        <p class="error-message">{{ $message }}</p>
    @enderror

    @error('requested_clock_out_at')
        <p class="error-message">{{ $message }}</p>
    @enderror

    @foreach ($attendance->breakTimes as $index => $break)
    <div class="detail-row">
        <div class="detail-label">休憩{{ $index + 1 }}</div>
        <div class="detail-value detail-time-range">
            <input class="time-box"
                type="time"
                name="break_start_at[]"
                value="{{ old('break_start_at.' . $index, $break->break_start_at ? \Carbon\Carbon::parse($break->break_start_at)->format('H:i') : '') }}">

            <span class="time-separator">〜</span>

            <input class="time-box"
                type="time"
                name="break_end_at[]"
                value="{{ old('break_end_at.' . $index, $break->break_end_at ? \Carbon\Carbon::parse($break->break_end_at)->format('H:i') : '') }}">
        </div>
    </div>
    @error('break_start_at.' . $index)
        <p class="error-message">{{ $message }}</p>
    @enderror

    @error('break_end_at.' . $index)
        <p class="error-message">{{ $message }}</p>
    @enderror

    @endforeach

    <div class="detail-row">
        <div class="detail-label">備考</div>
        <div class="detail-value">
            <textarea class="note-box" name="reason">{{ old('reason', $pendingRequest->reason ?? '') }}</textarea>
        </div>
    </div>
    @error('reason')
        <p class="error-message">{{ $message }}</p>
    @enderror

</div>

<div class="detail-action">
    @if ($isPending)
        <p class="pending-message">※承認待ちのため修正はできません。</p>
    @else
        <button class="btn-detail-edit">修正</button>
    @endif
</div>
    </form>
@endsection