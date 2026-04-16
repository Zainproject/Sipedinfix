<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PoktanController;
use App\Http\Controllers\SptController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\DanaMasukController;
use App\Http\Controllers\RekapSuratKeluarController;
use App\Http\Controllers\RekapAnggaranController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('home')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:ketua,sekretaris,bendahara'])->group(function () {
    Route::get('/index', [DashboardController::class, 'index'])->name('home');
});

Route::middleware(['auth', 'role:ketua'])->group(function () {
    Route::resource('petugas', PetugasController::class);
    Route::resource('poktan', PoktanController::class);

    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/petugas', [ImportController::class, 'importPetugas'])->name('petugas');
        Route::post('/poktan', [ImportController::class, 'importPoktan'])->name('poktan');
        Route::post('/spt', [ImportController::class, 'importSpt'])->name('spt');

        Route::get('/export/petugas', [ImportController::class, 'exportPetugas'])->name('export.petugas');
        Route::get('/export/poktan', [ImportController::class, 'exportPoktan'])->name('export.poktan');
        Route::get('/export/spt', [ImportController::class, 'exportSpt'])->name('export.spt');
    });
});

Route::middleware(['auth', 'role:ketua,sekretaris,bendahara'])->group(function () {

    Route::get('spt', [SptController::class, 'index'])->name('spt.index');
    Route::get('spt/print/{id?}', [SptController::class, 'print'])->name('spt.print');

    Route::get('spt/download-word/{id}', [SptController::class, 'downloadWord'])
        ->name('spt.downloadWord');
    Route::middleware('role:ketua,sekretaris')->group(function () {
        Route::get('spt/create', [SptController::class, 'create'])->name('spt.create');
        Route::post('spt', [SptController::class, 'store'])->name('spt.store');
        Route::get('spt/{spt}/edit', [SptController::class, 'edit'])->name('spt.edit');
        Route::put('spt/{spt}', [SptController::class, 'update'])->name('spt.update');
        Route::delete('spt/{spt}', [SptController::class, 'destroy'])->name('spt.destroy');
    });
});

Route::middleware(['auth', 'role:ketua,sekretaris'])->group(function () {
    Route::prefix('rekap-surat-keluar')->name('rekap-surat-keluar.')->group(function () {
        Route::get('/', [RekapSuratKeluarController::class, 'index'])->name('index');
        Route::get('/print', [RekapSuratKeluarController::class, 'print'])->name('print');
    });
});

Route::middleware(['auth', 'role:ketua,bendahara'])->group(function () {

    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::get('/', [KeuanganController::class, 'index'])->name('index');
        Route::get('/create/{spt_id}', [KeuanganController::class, 'create'])->name('create');
        Route::post('/store/{spt_id}', [KeuanganController::class, 'store'])->name('store');
        Route::get('/show/{spt_id}', [KeuanganController::class, 'show'])->name('show');
        Route::get('/edit/{spt_id}', [KeuanganController::class, 'edit'])->name('edit');
        Route::put('/update/{spt_id}', [KeuanganController::class, 'update'])->name('update');
        Route::patch('/update-status/{spt_id}', [KeuanganController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/destroy/{spt_id}', [KeuanganController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dana-masuk')->name('dana-masuk.')->group(function () {
        Route::get('/', [DanaMasukController::class, 'index'])->name('index');
        Route::get('/create', [DanaMasukController::class, 'create'])->name('create');
        Route::post('/store', [DanaMasukController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [DanaMasukController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [DanaMasukController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [DanaMasukController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('rekap-anggaran')->name('rekap-anggaran.')->group(function () {
        Route::get('/', [RekapAnggaranController::class, 'index'])->name('index');
        Route::get('/print', [RekapAnggaranController::class, 'print'])->name('print');
    });
});

Route::middleware(['auth', 'role:ketua,sekretaris,bendahara'])->group(function () {

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
    });

    Route::prefix('activity')->name('activity.')->group(function () {
        Route::get('/navbar', [ActivityController::class, 'navbar'])->name('navbar');
        Route::match(['POST', 'DELETE'], '/clear', [ActivityController::class, 'clear'])->name('clear');
    });

    Route::get('/search', [SearchController::class, 'index'])->name('search');
});

require __DIR__ . '/auth.php';
