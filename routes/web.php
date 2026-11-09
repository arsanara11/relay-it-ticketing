<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\TicketCategoryController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\TicketBillingController;
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
            Route::resource('ticket-categories', TicketCategoryController::class);
            Route::resource('tickets', AdminTicketController::class);

            Route::patch(
                '/tickets/{ticket}/diagnosis-decision',
                [AdminTicketController::class, 'decideDiagnosis']
            )->name('tickets.diagnosis.decide');

            Route::post(
                '/tickets/{ticket}/costs',
                [TicketBillingController::class, 'storeCost']
            )->name('tickets.costs.store');

            Route::delete(
                '/tickets/{ticket}/costs/{cost}',
                [TicketBillingController::class, 'destroyCost']
            )->name('tickets.costs.destroy');

            Route::post(
                '/tickets/{ticket}/payments',
                [TicketBillingController::class, 'storePayment']
            )->name('tickets.payments.store');

            Route::post(
                '/tickets/{ticket}/handover',
                [TicketBillingController::class, 'storeHandover']
            )->name('tickets.handover.store');
        });

    Route::prefix('technician')
        ->name('technician.')
        ->middleware('role:technician')
        ->group(function () {
            Route::get(
                '/dashboard',
                [TechnicianDashboard::class, 'index']
            )->name('dashboard');

            Route::get(
                '/tickets',
                [TechnicianTicketController::class, 'index']
            )->name('tickets.index');

            Route::get(
                '/tickets/{ticket}',
                [TechnicianTicketController::class, 'show']
            )->name('tickets.show');

            Route::post(
                '/tickets/{ticket}/diagnosis',
                [TechnicianTicketController::class, 'storeDiagnosis']
            )->name('tickets.diagnosis.store');

            Route::post(
                '/tickets/{ticket}/notes',
                [TechnicianTicketController::class, 'storeNote']
            )->name('tickets.notes.store');

            Route::patch(
                '/tickets/{ticket}/status',
                [TechnicianTicketController::class, 'updateStatus']
            )->name('tickets.status.update');
        });
});
