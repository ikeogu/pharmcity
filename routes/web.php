<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\POSController;
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


Route::middleware(['auth'])->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/fetch-states/{country}', [UserController::class, 'fetchStatesByCountryID']);
Route::get('/fetch-cities/{state}', [UserController::class, 'fetchCitiesByStateID']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['auth'])->group(function () {


        // Reporter Routes
        // Patient Management Routes
        Route::controller(PatientController::class)->prefix('patients')->name('patients.')->group(function () {
            // Main CRUD
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{patient}', 'show')->name('show');
            Route::get('/{patient}/edit', 'edit')->name('edit');
            Route::patch('/{patient}', 'update')->name('update');
            Route::delete('/{patient}', 'destroy')->name('destroy');

            // Additional Actions
            Route::get('/search/query', 'search')->name('search');
            Route::patch('/{patient}/status', 'updateStatus')->name('updateStatus');
            Route::post('/batch-import', 'batchImport')->name('batchImport');
            Route::get('/export/excel', 'export')->name('export');
            Route::post('/{patient}/restore', 'restore')->name('restore')->withTrashed();
            Route::get('stats',  'stats')->name('patients.stats');
        });

        // SuperAdmin Routes
        Route::middleware([])->group(function () {
            Route::resource('users', UserController::class);
            Route::get('staffs/search', [UserController::class, 'search'])->name('user.name');
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


        Route::prefix('pos')
            ->name('pos.')
            ->controller(POSController::class)
            ->group(function () {

                // Dashboard
                Route::get('/', 'index')->name('index');

                // Sales
                Route::get('/sales', 'sales')->name('sales');
                Route::get('/sales/{sale}', 'show')->name('show');
                Route::get('/sales/{sale}/print', 'print')->name('print');
                Route::post('/sales', 'store')->name('store');
                Route::post('/sales/{sale}/cancel', 'cancelSale')->name('cancel');

                // Drug search
                Route::get('/drugs/search', 'searchDrugs')->name('drugs.search');

                // Hold / Retrieve sales
                Route::post('/hold', 'holdSale')->name('hold');
                Route::get('/held-sales', 'retrieveHeldSales')->name('held');

                // Customers & Prescriptions
                Route::get('/customers', 'customers')->name('customers');
                Route::get('/prescriptions', 'prescriptions')->name('prescriptions');
                Route::get('/resume/{sale}',  'resume')->name('resume');
                Route::put('finalize/{sale}', 'finalize')->name('finalize');
            });
    });
});

require __DIR__ . '/auth.php';
