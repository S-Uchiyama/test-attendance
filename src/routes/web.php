<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 認証前
Route::get('/', function () {
    return redirect('/login');
});

// 管理者ログイン
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
});

// 一般ユーザー機能
Route::middleware('auth')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.break_in');
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.break_out');

    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail');
    Route::post('/attendance/detail/{id}/request', [AttendanceCorrectionRequestController::class, 'store'])
    ->name('attendance.request.store');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
    ->name('stamp_correction_request.list');

});

// 管理者機能
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // 仮: 管理者メニュー用
    Route::get('/attendance/list', function () {
        return 'admin attendance list';
    })->name('admin.attendance.list');

    Route::get('/staff/list', function () {
        return 'admin staff list';
    })->name('admin.staff.list');
});
