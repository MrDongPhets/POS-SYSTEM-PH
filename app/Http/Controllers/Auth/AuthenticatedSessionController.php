<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Master\Company;
use App\Models\Master\SystemUser;
use App\Models\Client\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request)
    {
        // First, try to authenticate as system user
        if ($this->authenticateSystemUser($request)) {
            return redirect()->intended(route('master.dashboard'));
        }

        // If system auth fails, try client authentication
        if ($this->authenticateClientUser($request)) {
            return redirect()->intended(route('client.dashboard'));
        }

        // If both fail, return error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    private function authenticateSystemUser(LoginRequest $request)
    {
        try {
            // Try to find system user first
            $systemUser = SystemUser::where('email', $request->email)->first();
            
            if ($systemUser && Hash::check($request->password, $systemUser->password) && $systemUser->is_active) {
                Auth::guard('master')->login($systemUser, $request->boolean('remember'));
                $request->session()->regenerate();
                return true;
            }
        } catch (\Exception $e) {
            Log::error("Error authenticating system user: " . $e->getMessage());
        }
        
        return false;
    }

    private function authenticateClientUser(LoginRequest $request)
    {
        try {
            // Get all active companies to check their databases
            $companies = Company::where('is_active', true)->get();
            
            foreach ($companies as $company) {
                // Set client database connection
                $this->setClientConnection($company->database_name);
                
                try {
                    $user = User::on('client')->where('email', $request->email)->first();
                    
                    if ($user && Hash::check($request->password, $user->password) && $user->is_active) {
                        Auth::login($user, $request->boolean('remember'));
                        $request->session()->regenerate();
                        $request->session()->put('company_id', $company->id);
                        $request->session()->put('company_name', $company->company_name);
                        $request->session()->put('database_name', $company->database_name);
                        
                        return true;
                    }
                } catch (\Exception $e) {
                    // Log error and continue to next company
                    Log::error("Error checking user in company {$company->id}: " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error authenticating client user: " . $e->getMessage());
        }
        
        return false;
    }

    private function setClientConnection($databaseName)
    {
        Config::set('database.connections.client', [
            'driver' => 'pgsql',
            'host' => env('DB_CLIENT_HOST', '127.0.0.1'),
            'port' => env('DB_CLIENT_PORT', '5432'),
            'database' => $databaseName,
            'username' => env('DB_CLIENT_USERNAME', 'postgres'),
            'password' => env('DB_CLIENT_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => 'prefer',
        ]);

        // Clear any cached connection
        DB::purge('client');
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('master')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}