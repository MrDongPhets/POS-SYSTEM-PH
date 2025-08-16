<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Http\Controllers\Master\DashboardController as MasterDashboardController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;

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
    Route::get('dashboard', [MasterDashboardController::class, 'index'])->name('dashboard');
});

// Client/Tenant routes
Route::middleware(['auth', 'tenant'])->prefix('client')->name('client.')->group(function () {
    Route::get('dashboard', [ClientDashboardController::class, 'index'])->name('dashboard');
});

Route::get('/test-password', function () {
    $email = 'admin@possystem.com';
    
    // Check if user exists in master database
    $systemUser = \App\Models\Master\SystemUser::where('email', $email)->first();
    
    if (!$systemUser) {
        return response()->json(['error' => 'System user not found']);
    }
    
    // Test different passwords
    $passwords = ['password', 'password123', 'admin', 'Password', ''];
    $results = [];
    
    foreach ($passwords as $testPassword) {
        $matches = \Illuminate\Support\Facades\Hash::check($testPassword, $systemUser->password);
        $results[$testPassword] = $matches;
        if ($matches) {
            $results['WINNER'] = $testPassword;
        }
    }
    
    return response()->json([
        'user_found' => true,
        'user_id' => $systemUser->id,
        'user_name' => $systemUser->name,
        'user_email' => $systemUser->email,
        'user_active' => $systemUser->is_active,
        'user_role' => $systemUser->role,
        'password_hash' => substr($systemUser->password, 0, 40) . '...',
        'password_tests' => $results,
        'created_at' => $systemUser->created_at,
        'migration_expected_hash' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'hashes_match' => $systemUser->password === '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ]);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';