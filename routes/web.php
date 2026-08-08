<?php

use App\Enums\Role;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return match (request()->user()->role) {
            Role::Admin => redirect()->route('admin.dashboard'),
            Role::Cleaner => redirect()->route('cleaner.dashboard'),
            Role::Customer => redirect()->route('customer.dashboard'),
        };
    })->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/', 'pages::admin.dashboard')->name('dashboard');
        Route::livewire('jobs/{job}', 'pages::admin.job')->name('jobs.show');
        Route::livewire('cleaners', 'pages::admin.cleaners')->name('cleaners');
        Route::livewire('customers', 'pages::admin.customers')->name('customers');
    });

    Route::middleware('role:cleaner')->prefix('cleaner')->name('cleaner.')->group(function () {
        Route::livewire('/', 'pages::cleaner.dashboard')->name('dashboard');
    });

    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::livewire('/', 'pages::customer.dashboard')->name('dashboard');
        Route::livewire('upcoming-jobs', 'pages::customer.upcoming-jobs')->name('upcoming-jobs');
        Route::livewire('job-history', 'pages::customer.job-history')->name('job-history');
        Route::livewire('jobs/{job}/edit', 'pages::customer.job-edit')->name('jobs.edit');
    });
});

require __DIR__.'/settings.php';
