<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
use App\Http\Controllers\Admin\ApprovalController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// 一般ユーザー 認証
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('auth.logout');

Route::get('/register', [AuthController::class, 'showRegister'])
    ->name('register.form');

Route::post('/register', [AuthController::class, 'register'])
    ->name('register');

Route::get('/email/verify', [AuthController::class, 'showVerifyEmail'])
    ->middleware('auth')
    ->name('verification.notice');

// メール内リンクを踏んだとき
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()
        ->route('attendance.index')
        ->with('verified', true);
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:1,1'])->name('verification.send');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clockOut');
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.breakStart');
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.breakEnd');
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.show');
    Route::get('/stamp_correction_requests', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_requests.index');
        Route::get('/stamp_correction_requests/{id}',
    [StampCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_requests.show');
    Route::post('/stamp_correction_requests', [StampCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_requests.store');
    Route::get('/stamp_correction_requests/create/{attendance}',
    [StampCorrectionRequestController::class, 'create'])
        ->name('stamp_correction_requests.create');
});

Route::prefix('admin')->group(function () {

    // 管理者ログイン
    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->name('admin.logout');

    // 管理者ログイン後
    Route::middleware(['auth:admin'])
        ->name('admin.')
        ->group(function () {

            Route::get('/attendance/list',
                [AdminAttendanceController::class, 'index']
            )->name('attendance.list');

            Route::get('/attendance/{id}',
                [AdminAttendanceController::class, 'detail']
            )->name('attendance.detail');

            Route::get('/staff/list',
                [AdminStaffController::class, 'index']
            )->name('staff.list');

            Route::get('/stamp_correction_requests/list',
                [AdminStampCorrectionRequestController::class, 'index']
            )->name('stamp_correction_requests.list');
        });
});