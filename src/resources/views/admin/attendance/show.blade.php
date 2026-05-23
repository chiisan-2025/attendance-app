@extends('layouts.admin')

@section('title','勤怠詳細')

@section('content')


    <h1 class="page-title">勤怠詳細</h1>
    @if ($errors->any())
    <div style="color: red;">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    @php
        $break1 = $attendance->breakTimes[0] ?? null;
        $break2 = $attendance->breakTimes[1] ?? null;
    @endphp

<form method="POST"
    action="{{ url('/admin/attendance/' . $attendance->id . '/update') }}">

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
                <input type="time" name="requested_clock_in_at"
                    value="{{ $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '' }}">

                <span class="time-separator">〜</span>

                <input type="time" name="requested_clock_out_at"
                    value="{{ $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '' }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">休憩</div>
            <div class="detail-value detail-time-range">
                <input type="time" name="break_start_at[]"
                    value="{{ $break1 && $break1->break_start_at ? \Carbon\Carbon::parse($break1->break_start_at)->format('H:i') : '' }}">

                <span class="time-separator">〜</span>
                <input type="time" name="break_end_at[]"
                    value="{{ $break1 && $break1->break_end_at ? \Carbon\Carbon::parse($break1->break_end_at)->format('H:i') : '' }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">休憩2</div>
            <div class="detail-value detail-time-range">
                <input type="time" name="break_start_at[]"
                    value="{{ $break2 && $break2->break_start_at ? \Carbon\Carbon::parse($break2->break_start_at)->format('H:i') : '' }}">
                <span class="time-separator">〜</span>
                <input type="time" name="break_end_at[]"
                    value="{{ $break2 && $break2->break_end_at ? \Carbon\Carbon::parse($break2->break_end_at)->format('H:i') : '' }}">
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-label">備考</div>
            <div class="detail-value">
                <textarea name="reason" class="note-box">{{ old('reason', $attendance->note ?? '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="detail-action">
        <button type="submit" class="btn-detail-edit">修正</button>
    </div>
</form>
@endsection