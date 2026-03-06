<?php
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StampCorrectionRequestController as AdminStampCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffAttendanceController;

// 一般ユーザー 認証
Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');
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
    Route::prefix('attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])
            ->name('attendance.index');
        Route::get('/list', [AttendanceController::class, 'list'])
            ->name('attendance.list');
        Route::get('/detail/{date}',[AttendanceController::class, 'detail']
            )->name('attendance.detail');
        Route::post('/clock-in', [AttendanceController::class, 'clockIn'])
            ->name('attendance.clockIn');
        Route::post('/clock-out', [AttendanceController::class, 'clockOut'])
            ->name('attendance.clockOut');
        Route::post('/break-start', [AttendanceController::class, 'breakStart'])
            ->name('attendance.breakStart');
        Route::post('/break-end', [AttendanceController::class, 'breakEnd'])
            ->name('attendance.breakEnd');
    });

    Route::prefix('stamp_correction_request')->group(function () {
        Route::post('/',[StampCorrectionRequestController::class, 'store'])
            ->name('stamp_correction_request.store');
        Route::get('/list', [StampCorrectionRequestController::class, 'index'])
            ->name('stamp_correction_request.index');
    });
});


Route::prefix('admin')->group(function () {

    // 管理者ログイン
    Route::get('/login', [AdminAuthController::class, 'showLogin'])
        ->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->name('admin.logout');

    // 管理者ログイン後
    Route::middleware(['ensure.admin'])
        ->name('admin.')
        ->group(function () {

        Route::prefix('attendance')->group(function () {

            Route::get('/list',
                [AdminAttendanceController::class, 'index']
            )->name('attendance.list');

            Route::get('/{id}',
                [AdminAttendanceController::class, 'detail']
            )->name('attendance.detail');

            Route::put('/{id}/update',
                [AdminAttendanceController::class, 'update']
            )->name('attendance.update');
        });

        Route::prefix('staff')->group(function () {
            Route::get('/list', [StaffController::class, 'list'])
                ->name('staff.list');

            // スタッフ別 勤怠一覧
            Route::get('/{user}/attendances',
                [StaffAttendanceController::class, 'index']
            )->name('staff.attendance.index');

            // CSV出力
            Route::get('/{user}/attendances/csv',
                [StaffAttendanceController::class, 'csv']
            )->name('staff.attendance.csv');
        });

        Route::prefix('stamp_correction_request')->group(function () {
            Route::get('/list',
                [AdminStampCorrectionRequestController::class, 'index']
            )->name('stamp_correction_request.index');

            Route::get('/approve/{id}',
                [AdminStampCorrectionRequestController::class, 'edit']
            )->name('stamp_correction_request.edit');

            Route::post('/approve/{id}',
                [AdminStampCorrectionRequestController::class, 'approve']
            )->name('stamp_correction_request.approve');
        });
    });
});


