<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\StampCorrectionRequestBreak;


class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $status = $attendance ? $attendance->status : '勤務外';

        $breaks = $attendance ? $attendance->breakTimes : collect();
        $now = Carbon::now();

        return view('attendance.index', compact(
            'user',
            'attendance',
            'status',
            'breaks',
            'now'
        ));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in_at' => now(),
            'status' => '出勤中',
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out_at' => now(),
                'status' => '退勤済',
            ]);
        }

        return redirect('/attendance');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance && $attendance->status === '出勤中') {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => now(),
            ]);

            $attendance->update([
                'status' => '休憩中',
            ]);
        }

        return redirect('/attendance');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance && $attendance->status === '休憩中') {
            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end_at')
                ->latest()
                ->first();

            if ($break) {
                $break->update([
                    'break_end_at' => now(),
                ]);
            }

            $attendance->update([
                'status' => '出勤中',
            ]);
        }

        return redirect('/attendance');
    }

    public function list()
    {
        $user = Auth::user();
        $month = request('month', Carbon::now()->format('Y-m'));

        $currentMonth = Carbon::createFromFormat('Y-m', $month);
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breakTimes')->where('user_id', $user->id)
            ->where('work_date', 'like', $currentMonth->format('Y-m') . '%')
            ->orderBy('work_date', 'asc')
            ->get();

            foreach ($attendances as $attendance) {
                $totalMinutes = 0;

                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start_at && $break->break_end_at) {
                        $start = Carbon::parse($break->break_start_at);
                        $end = Carbon::parse($break->break_end_at);
                        $totalMinutes +=    $start->diffInMinutes($end);
                    }
                }

                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;

                $attendance->total_break_time = sprintf('%02d:%02d', $hours, $minutes);

                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $workStart = Carbon::parse($attendance->clock_in_at);
                    $workEnd = Carbon::parse($attendance->clock_out_at);

                    $workMinutes = $workStart->diffInMinutes($workEnd) - $totalMinutes;

                    $hours = floor($workMinutes / 60);
                    $minutes = $workMinutes % 60;

                    $attendance->total_work_time = sprintf('%02d:%02d', $hours, $minutes);
                } else {
                    $attendance->total_work_time = '';
                }
            }

        return view('attendance.list', compact(
            'user',
            'attendances',
            'currentMonth',
            'previousMonth',
            'nextMonth'
        ));
    }

    public function adminList()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $date = request('date', Carbon::today()->toDateString());

        $currentDate = Carbon::parse($date);
        $previousDate = $currentDate->copy()->subDay()->toDateString();
        $nextDate = $currentDate->copy()->addDay()->toDateString();

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->where('work_date', $currentDate->toDateString())
            ->orderBy('user_id')
            ->get();

        foreach ($attendances as $attendance) {
            $totalBreakMinutes = 0;

            foreach ($attendance->breakTimes as $break) {
                if ($break->break_start_at && $break->break_end_at) {
                    $start = Carbon::parse($break->break_start_at);
                    $end = Carbon::parse($break->break_end_at);
                    $totalBreakMinutes += $start->diffInMinutes($end);
                }
            }

            $breakHours = floor($totalBreakMinutes / 60);
            $breakMinutes = $totalBreakMinutes % 60;
            $attendance->total_break_time = sprintf('%d:%02d', $breakHours, $breakMinutes);

            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                $workStart = Carbon::parse($attendance->clock_in_at);
                $workEnd = Carbon::parse($attendance->clock_out_at);
                $totalWorkMinutes = $workStart->diffInMinutes($workEnd) - $totalBreakMinutes;

                $workHours = floor($totalWorkMinutes / 60);
                $workMinutes = $totalWorkMinutes % 60;
                $attendance->total_work_time = sprintf('%d:%02d', $workHours, $workMinutes);
            } else {
                $attendance->total_work_time = '';
            }
        }

        return view('admin.attendance.list', compact(
            'attendances',
            'currentDate',
            'previousDate',
            'nextDate'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('breakTimes', 'user')
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->first();

        $isPending = $pendingRequest !== null;

        return view('attendance.show', compact('attendance', 'isPending', 'pendingRequest'));
    }

    public function requestCorrection(AttendanceCorrectionRequest $request, $id)
    {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $correction = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => $request->requested_clock_in_at,
            'requested_clock_out_at' => $request->requested_clock_out_at,
            'reason' => $request->reason,
            'status' => '承認待ち',
            'requested_at' => now(),
        ]);

        foreach ($request->break_start_at as $index => $start) {
            if ($start || ($request->break_end_at[$index] ?? null)) {
                StampCorrectionRequestBreak::create([
                    'stamp_correction_request_id' => $correction->id,
                    'requested_break_start_at' => $start,
                    'requested_break_end_at' => $request->break_end_at[$index] ?? null,
                ]);
            }
        }

        return redirect('/attendance/list')->with('message', '申請しました');
    }

    public function requestList()
    {
        $user = Auth::user();
        $status = request('status', '承認待ち');

        $requests = StampCorrectionRequest::with('attendance')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('stamp_correction_request.index', compact('requests', 'status'));
    }

    public function adminRequestList()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $status = request('status', '承認待ち');

        $requests = StampCorrectionRequest::with('attendance', 'user')
            ->where('status', $status)
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request.index', compact('requests','status'));
    }

    public function approveRequest($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $request = StampCorrectionRequest::with('stampCorrectionRequestBreaks')->findOrFail($id);

        // 勤怠更新
        $attendance = $request->attendance;

        $attendance->update([
            'clock_in_at' => $request->requested_clock_in_at,
            'clock_out_at' =>         $request->requested_clock_out_at,
        ]);

        $attendance->breakTimes()->delete();
        foreach ($request->stampCorrectionRequestBreaks as $requestBreak) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => $requestBreak->requested_break_start_at,
                'break_end_at' => $requestBreak->requested_break_end_at,
            ]);
        }

        // ステータス更新
        $request->update([
            'status' => '承認済み',
            'processed_at' => now(),
        ]);

        return redirect('/admin/stamp_correction_request/list?status=承認済み');
    }

    public function adminRequestShow($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $request = StampCorrectionRequest::with([
            'attendance.user',
            'attendance.breakTimes',
            'stampCorrectionRequestBreaks'])
            ->findOrFail($id);

        return view('admin.stamp_correction_request.show', compact('request'));
    }

    public function adminShow($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
        abort(403);
        }

        $attendance = Attendance::with([
            'user',
            'breakTimes'
        ])->findOrFail($id);

        return view(
            'admin.attendance.show',
            compact('attendance')
        );
    }

    public function adminStaffMonthly($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $month = request('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);
        $previousMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $staff = \App\Models\User::findOrFail($id);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $staff->id)
            ->where('work_date', 'like', $currentMonth->format('Y-m') . '%')
            ->orderBy('work_date', 'asc')
            ->get();

        foreach ($attendances as $attendance) {
            $totalBreakMinutes = 0;

            foreach ($attendance->breakTimes as $break) {
                if ($break->break_start_at && $break->break_end_at) {
                    $start = Carbon::parse($break->break_start_at);
                    $end = Carbon::parse($break->break_end_at);
                    $totalBreakMinutes += $start->diffInMinutes($end);
                }
            }

            $breakHours = floor($totalBreakMinutes / 60);
            $breakMinutes = $totalBreakMinutes % 60;
            $attendance->total_break_time = sprintf('%d:%02d', $breakHours, $breakMinutes);

            if ($attendance->clock_in_at && $attendance->clock_out_at) {
                $workStart = Carbon::parse($attendance->clock_in_at);
                $workEnd = Carbon::parse($attendance->clock_out_at);
                $totalWorkMinutes = $workStart->diffInMinutes($workEnd) - $totalBreakMinutes;

                $workHours = floor($totalWorkMinutes / 60);
                $workMinutes = $totalWorkMinutes % 60;
                $attendance->total_work_time = sprintf('%d:%02d', $workHours, $workMinutes);
            } else {
                $attendance->total_work_time = '';
            }
        }

        return view('admin.attendance.staff_monthly', compact(
            'staff',
            'attendances',
            'currentMonth',
            'previousMonth',
            'nextMonth'
        ));
    }

    public function adminStaffList()
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $staffs = \App\Models\User::where('role', 'user')->get();

        return view('admin.staff.index', compact('staffs'));
    }

    public function adminStaffCsv($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            abort(403);
        }

        $month = request('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month);

        $staff = \App\Models\User::findOrFail($id);

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $staff->id)
            ->where('work_date', 'like', $currentMonth->format('Y-m') . '%')
            ->orderBy('work_date', 'asc')
            ->get();

        $filename = $staff->name . '_' . $currentMonth->format('Y-m') . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $attendance) {
                $totalBreakMinutes = 0;

                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start_at && $break->break_end_at) {
                        $start = Carbon::parse($break->break_start_at);
                        $end = Carbon::parse($break->break_end_at);
                        $totalBreakMinutes += $start->diffInMinutes($end);
                    }
                }

                $breakTime = sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                if ($attendance->clock_in_at && $attendance->clock_out_at) {
                    $workStart = Carbon::parse($attendance->clock_in_at);
                    $workEnd = Carbon::parse($attendance->clock_out_at);
                    $workMinutes = $workStart->diffInMinutes($workEnd) - $totalBreakMinutes;
                    $workTime = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                } else {
                    $workTime = '';
                }

                fputcsv($handle, [
                    Carbon::parse($attendance->work_date)->format('Y/m/d'),
                    $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '',
                    $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '',
                    $breakTime,
                    $workTime,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function adminUpdate(
        AttendanceCorrectionRequest $request,
        $id
    )
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->update([
            'clock_in_at' =>
                $request->requested_clock_in_at,

            'clock_out_at' =>
                $request->requested_clock_out_at,

            'note' =>
                $request->reason,
        ]);

        $attendance->breakTimes()->delete();

        foreach ($request->break_start_at as $index => $start){

            if($start || ($request->break_end_at[$index] ?? null)){

                BreakTime::create([
                    'attendance_id'=>$attendance->id,
                    'break_start_at'=>$start,
                    'break_end_at'=>$request->break_end_at[$index] ?? null
                    ]);
                }
            }


        return redirect('/admin/attendance/list?date=' . $attendance->work_date)
            ->with('message','修正しました');

    }
}