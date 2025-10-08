<?php

use App\Http\Controllers\DrugController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/fetch-states/{country}', [UserController::class, 'fetchStatesByCountryID']);
Route::get('/fetch-cities/{state}', [UserController::class, 'fetchCitiesByStateID']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth'])->group(function () {


        // Reporter Routes
        Route::middleware('role:reporter')->group(function () {
            // Route::resource('patients', PatientController::class);
        });

        // SuperAdmin Routes
        Route::middleware([])->group(function () {
            Route::resource('users', UserController::class);
        });


        // Drug Management Routes
        Route::controller(DrugController::class)->prefix('drugs')->name('drugs.')->group(function () {
            // List and views
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{drug}', 'show')->name('show');
            Route::get('/{drug}/edit', 'edit')->name('edit');
            Route::put('/{drug}', 'update')->name('update');
            Route::delete('/{drug}', 'destroy')->name('destroy');

            // Special views
            Route::get('/expiring/list', 'expiring')->name('expiring');
            Route::get('/low-stock/list', 'lowStock')->name('lowStock');

            // Actions
            Route::get('/search/query', 'search')->name('search');
            Route::post('/{drug}/update-stock', 'updateStock')->name('updateStock');
            Route::post('/bulk-update-stock', 'bulkUpdateStock')->name('bulkUpdateStock');
            Route::post('/{drug}/restore', 'restore')->name('restore')->withTrashed();
            Route::delete('/{drug}/force', 'forceDestroy')->name('forceDestroy')->withTrashed();
        });
    });
});

require __DIR__ . '/auth.php';
