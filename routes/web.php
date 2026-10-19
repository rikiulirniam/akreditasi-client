<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TenagaKependidikanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (session()->has('access_token')) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.akademik')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('tenaga-kependidikan', TenagaKependidikanController::class);
    Route::get('/tenaga-pendidikan', [TenagaKependidikanController::class, 'index'])->name('tenaga-pendidikan.index');
    Route::get('/tenaga-pendidikan/{tenaga_kependidikan}', [TenagaKependidikanController::class, 'show'])->name('tenaga-pendidikan.show');
    Route::post('/tenaga-pendidikan/{tendikId}/sertifikasi', [TenagaKependidikanController::class, 'storeSertifikasi'])->name('tenaga-pendidikan.sertifikasi.store');
    Route::put('/tenaga-pendidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'updateSertifikasi'])->name('tenaga-pendidikan.sertifikasi.update');
    Route::delete('/tenaga-pendidikan/{tendikId}/sertifikasi/{sertifikasiId}', [TenagaKependidikanController::class, 'destroySertifikasi'])->name('tenaga-pendidikan.sertifikasi.destroy');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::put('/permissions/module/{module}', [PermissionController::class, 'updateModule'])->name('permissions.update-module');
    Route::delete('/permissions/module/{module}', [PermissionController::class, 'destroyModule'])->name('permissions.destroy-module');
    Route::resource('permissions', PermissionController::class)->except(['show']);
});
