<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
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

// 管理者ログイン・ログアウト画面
Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', function () {
        return 'admin attendance list';
    });
});

// 確認用
Route::get('/attendance', function () {
    return view('auth.attendance-test');
})->middleware('auth');

Route::get('/', function () {
    return redirect('/login');
});
