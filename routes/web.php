
<?php

use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\TicketCategoryController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;

use App\Http\Controllers\Technician\DashboardController as TechnicianDashboard;
use App\Http\Controllers\Technician\TicketController as TechnicianTicketController;

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'login'])
        ->middleware('throttle:5,1');

});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return match (auth()->user()->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'technician' => redirect()->route('technician.dashboard'),
            default => abort(403),
        };
    })->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])
        ->name('logout');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:admin')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboard::class, 'index'])
                ->name('dashboard');

            Route::resource('customers', CustomerController::class);

            Route::resource('devices', DeviceController::class);

            Route::resource(
                'ticket-categories',
                TicketCategoryController::class
            );

            Route::resource('tickets', AdminTicketController::class);

        });

    Route::prefix('technician')
        ->name('technician.')
        ->middleware('role:technician')
        ->group(function () {

            Route::get('/dashboard', [TechnicianDashboard::class, 'index'])
                ->name('dashboard');

            Route::get('/tickets', [TechnicianTicketController::class, 'index'])
                ->name('tickets.index');

            Route::get('/tickets/{ticket}', [TechnicianTicketController::class, 'show'])
                ->name('tickets.show');

        });

});
