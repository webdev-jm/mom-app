<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;

use App\Http\Controllers\{
    RoleController, UserController, CompanyController,
    SystemLogController, SystemSettingController, HomeController,
    NotificationController, MomTypeController, MomController,
    FireAlarmController, ReportController, LocationController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes(['register' => false, 'reset' => false, 'verify' => false]);

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('lang/{locale}', function ($locale) {
    if (!in_array($locale, ['en', 'ja', 'zh-CN', 'fil'])) {
        abort(400);
    }
    session(['locale' => $locale]);
    return redirect()->back();
})->name('lang.switch');

Route::get('error-logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index')->middleware('auth');

Route::get('check-notification', function() {
    return new App\Notifications\MomSubmittedNotification();
});

Route::group(['middleware' => ['auth', 'optimizeImages']], function() {
    // PROFILE
    Route::get('profile/{id}', [UserController::class, 'profile'])->name('profile');

    // FIRE ALARM
    Route::get('fire-alarm', [FireAlarmController::class, 'index'])->name('fire-alarm')->middleware('permission:fire alarm access');

    // NOTIFICATION
    Route::get('test-notification', [NotificationController::class, 'testNotification'])->name('test-notification');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications');

    // REPORT ROUTES
    Route::group(['middleware' => 'permission:report access'], function() {
        Route::get('report', [ReportController::class, 'index'])->name('report.index');
    });

    // MOM ROUTES
    Route::group(['middleware' => 'permission:mom access'], function() {
        Route::get('moms', [MomController::class, 'index'])->name('mom.index');
        Route::get('mom/create', [MomController::class, 'create'])->name('mom.create')->middleware('permission:mom create');
        Route::get('mom/upload', [MomController::class, 'upload'])->name('mom.upload')->middleware('permission:mom upload');
        Route::get('mom/{id}', [MomController::class, 'show'])->name('mom.show');
        Route::get('mom/{id}/edit', [MomController::class, 'edit'])->name('mom.edit')->middleware('permission:mom edit');
        Route::get('mom-topic/{id}', [MomController::class, 'topic'])->name('mom.topic');

        Route::get('mom-print/{id}', [MomController::class, 'printPDF'])->name('mom.printPDF')->middleware('permission:mom print');
        Route::get('mom-export/{id}', [MomController::class, 'exportExcel'])->name('mom.exportExcel')->middleware('permission:mom print');
    });

    // MOM TYPES ROUTES
    Route::group(['middleware' => 'permission:type access'], function() {
        Route::get('mom-types', [MomTypeController::class, 'index'])->name('type.index');
        Route::get('mom-type/create', [MomTypeController::class, 'create'])->name('type.create')->middleware('permission:type create');
        Route::post('mom-type', [MomTypeController::class, 'store'])->name('type.store')->middleware('permission:type create');

        Route::get('mom-type/{id}', [MomTypeController::class, 'show'])->name('type.show');

        Route::get('mom-type/{id}/edit', [MomTypeController::class, 'edit'])->name('type.edit')->middleware('permission:type edit');
        Route::post('mom-type/{id}', [MomTypeController::class, 'update'])->name('type.update')->middleware('permission:type edit');
    });

    // COMPANIES ROUTES
    Route::group(['middleware' => 'permission:company access'], function() {
        Route::get('companies', [CompanyController::class, 'index'])->name('company.index');
        Route::get('company/create', [CompanyController::class, 'create'])->name('company.create')->middleware('permission:company create');
        Route::post('company', [CompanyController::class, 'store'])->name('company.store')->middleware('permission:company create');

        Route::get('company/{id}', [CompanyController::class, 'show'])->name('company.show');

        Route::get('company/{id}/edit', [CompanyController::class, 'edit'])->name('company.edit')->middleware('permission:company edit');
        Route::post('company/{id}', [CompanyController::class, 'update'])->name('company.update')->middleware('permission:company edit');
    });

    // LOCATIONS ROUTES
    Route::group(['middleware' => 'permission:location access'], function() {
        Route::get('locations', [LocationController::class, 'index'])->name('location.index');
        Route::get('location/create', [LocationController::class, 'create'])->name('location.create')->middleware('permission:location create');
        Route::post('location', [LocationController::class, 'store'])->name('location.store')->middleware('permission:location create');

        Route::get('location/{id}', [LocationController::class, 'show'])->name('location.show');

        Route::get('location/{id}/edit', [LocationController::class, 'edit'])->name('location.edit')->middleware('permission:location edit');
        Route::post('location/{id}', [LocationController::class, 'update'])->name('location.update')->middleware('permission:location edit');
    });

    // ROLES ROUTES
    Route::group(['middleware' => 'permission:role access'], function() {
        Route::get('roles', [RoleController::class, 'index'])->name('role.index');
        Route::get('role/create', [RoleController::class, 'create'])->name('role.create')->middleware('permission:role create');
        Route::post('role', [RoleController::class, 'store'])->name('role.store')->middleware('permission:role create');

        Route::get('role/{id}', [RoleController::class, 'show'])->name('role.show');

        Route::get('role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit')->middleware('permission:role edit');
        Route::post('role/{id}', [RoleController::class, 'update'])->name('role.update')->middleware('permission:role edit');
    });

    // USERS ROUTES
    Route::group(['middleware' => 'permission:user access'], function() {
        Route::get('users', [UserController::class, 'index'])->name('user.index');
        Route::get('user/create', [UserController::class, 'create'])->name('user.create')->middleware('permission:user create');
        Route::post('user', [UserController::class, 'store'])->name('user.store')->middleware('permission:user create');

        Route::get('user/{id}', [UserController::class, 'show'])->name('user.show');

        Route::get('user/{id}/edit', [UserController::class, 'edit'])->name('user.edit')->middleware('permission:user edit');
        Route::post('user/{id}', [UserController::class, 'update'])->name('user.update')->middleware('permission:user edit');
    });

    // SYSTEM SETTING
    Route::group(['middleware' => 'permission:system settings'], function() {
        Route::get('system-setting', [SystemSettingController::class, 'index'])->name('system-setting.index');
    });

    // SYSTEM LOG ROUTES
    Route::group(['middleware' => 'permission:system logs'], function() {
        Route::get('system-logs', [SystemLogController::class, 'index'])->name('system-logs');
    });

});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
