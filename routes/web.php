<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DosenKelasController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\KrsController;
use App\Http\Controllers\KrsApprovalController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\PengajuanKelasController;
use App\Http\Controllers\MataKuliahController;
use App\Http\Controllers\ProgramStudiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::resource('program-studi', ProgramStudiController::class)
            ->except(['show'])
            ->parameters(['program-studi' => 'programStudi']);
        Route::resource('mahasiswa', MahasiswaController::class)->except(['show']);
        Route::resource('pengajar', DosenController::class)->except(['show']);
        Route::resource('mata-kuliah', MataKuliahController::class)
            ->except(['show'])
            ->parameters(['mata-kuliah' => 'mataKuliah']);
        Route::resource('kelas', KelasController::class)
            ->except(['show'])
            ->parameters(['kelas' => 'kelas']);
        Route::put('/pengajuan-kelas/{pengajuanKelas}', [PengajuanKelasController::class, 'update'])
            ->name('pengajuan-kelas.update');
    });

    Route::middleware('role:dosen')->group(function () {
        Route::get('/kelas-saya', DosenKelasController::class)->name('dosen.kelas');
        Route::post('/kelas-saya/pengajuan', [DosenKelasController::class, 'store'])
            ->name('dosen.pengajuan-kelas.store');
        Route::put('/kelas-saya/pendaftaran/{krs}', [KrsApprovalController::class, 'update'])
            ->name('dosen.krs.update');
    });

    Route::middleware('role:mahasiswa')->group(function () {
        Route::get('/krs', [KrsController::class, 'index'])->name('mahasiswa.kelas');
        Route::post('/krs', [KrsController::class, 'store'])->name('mahasiswa.krs.store');
    });
});
