<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAuthController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm']);
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/attendance/list', [AttendanceController::class, 'list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show']);
    Route::post('/attendance/detail/{id}/request', [AttendanceController::class, 'requestCorrection']);
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList']);

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart']);
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd']);
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/attendance/list', [AttendanceController::class, 'adminList']);
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('/admin/stamp_correction_request/list', [AttendanceController::class, 'adminRequestList']);
    Route::get('/admin/stamp_correction_request/{id}', [AttendanceController::class, 'adminRequestShow']);
    Route::post('/admin/stamp_correction_request/{id}/approve', [AttendanceController::class, 'approveRequest']);
    Route::get('/admin/attendance/{id}', [AttendanceController::class, 'adminShow']);
    Route::post('/admin/attendance/{id}/update',[AttendanceController::class,'adminUpdate']);
    Route::get('/admin/staff/list', [AttendanceController::class, 'adminStaffList']);
    Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'adminStaffMonthly']);
    Route::get('/admin/attendance/staff/{id}/csv', [AttendanceController::class, 'adminStaffCsv']);
});