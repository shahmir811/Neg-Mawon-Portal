<?php

use App\Enums\Role;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\QuoteRequestController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::view('/become-a-cleaner', 'pages.become-a-cleaner')->name('become-a-cleaner');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
Route::view('/get-a-quote/residential', 'pages.get-a-quote-residential')->name('quotes.residential.create');
Route::post('/quotes/residential', [QuoteRequestController::class, 'residential'])->name('quotes.residential.store');
Route::view('/get-a-quote/commercial', 'pages.get-a-quote-commercial')->name('quotes.commercial.create');
Route::post('/quotes/commercial', [QuoteRequestController::class, 'commercial'])->name('quotes.commercial.store');

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
        Route::livewire('quote-requests', 'pages::admin.quote-requests')->name('quote-requests');
        Route::livewire('pricing', 'pages::admin.pricing')->name('pricing');
        Route::livewire('backups', 'pages::admin.backups')->name('backups');
    });

    Route::middleware('role:cleaner')->prefix('cleaner')->name('cleaner.')->group(function () {
        Route::livewire('/', 'pages::cleaner.dashboard')->name('dashboard');
    });

    Route::middleware('role:customer')->prefix('customer')->name('customer.')->group(function () {
        Route::livewire('/', 'pages::customer.dashboard')->name('dashboard');
        Route::livewire('upcoming-jobs', 'pages::customer.upcoming-jobs')->name('upcoming-jobs');
        Route::livewire('job-history', 'pages::customer.job-history')->name('job-history');
        Route::livewire('jobs/{job}', 'pages::customer.job-show')->name('jobs.show');
        Route::livewire('jobs/{job}/edit', 'pages::customer.job-edit')->name('jobs.edit');
    });
});

require __DIR__.'/settings.php';
