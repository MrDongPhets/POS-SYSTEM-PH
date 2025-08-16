<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

// Default dashboard route (will redirect based on user type)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = Auth::user();
        
        // Check if system user
        if ($user instanceof \App\Models\Master\SystemUser) {
            return redirect()->route('master.dashboard');
        }
        
        // Check if client user
        if ($user instanceof \App\Models\Client\User) {
            return redirect()->route('client.dashboard');
        }
        
        // Default dashboard for regular users
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Master/System Admin routes
Route::middleware(['auth:master'])->prefix('master')->name('master.')->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('master/dashboard');
    })->name('dashboard');
});

// Client/Tenant routes
Route::middleware(['auth', 'tenant'])->prefix('client')->name('client.')->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('client/dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';