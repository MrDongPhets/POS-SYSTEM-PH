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

    // REPLACE THIS ENTIRE METHOD
    public function store(LoginRequest $request)
    {
        Log::info("Login attempt for email: " . $request->email);
        
        // First, try to authenticate as system user
        if ($this->authenticateSystemUser($request)) {
            Log::info("Successfully authenticated as system user");
            return redirect()->intended(route('master.dashboard'));
        }

        // TEMPORARILY SKIP CLIENT AUTHENTICATION TO AVOID ENUM ISSUES
        // if ($this->authenticateClientUser($request)) {
        //     Log::info("Successfully authenticated as client user");
        //     return redirect()->intended(route('client.dashboard'));
        // }

        Log::warning("Authentication failed for email: " . $request->email);
        // If both fail, return error
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    private function authenticateSystemUser(LoginRequest $request)
    {
        try {
            Log::info("Attempting system user authentication for: " . $request->email);
            
            // Try to find system user first
            $systemUser = SystemUser::where('email', $request->email)->first();
            
            if (!$systemUser) {
                Log::info("No system user found with email: " . $request->email);
                return false;
            }
            
            Log::info("System user found - ID: {$systemUser->id}, Active: " . ($systemUser->is_active ? 'Yes' : 'No'));
            
            // Check if password matches
            $passwordMatches = Hash::check($request->password, $systemUser->password);
            Log::info("Password check result: " . ($passwordMatches ? 'Match' : 'No Match'));
            
            if ($passwordMatches && $systemUser->is_active) {
                Auth::guard('master')->login($systemUser, $request->boolean('remember'));
                $request->session()->regenerate();
                Log::info("System user logged in successfully");
                return true;
            }
            
            if (!$systemUser->is_active) {
                Log::warning("System user account is inactive");
            }
            
        } catch (\Exception $e) {
            Log::error("Error authenticating system user: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
        
        return false;
    }

    private function authenticateClientUser(LoginRequest $request)
    {
        try {
            Log::info("Attempting client user authentication for: " . $request->email);
            
            // Get all active companies to check their databases
            $companies = Company::where('is_active', true)->get();
            Log::info("Found " . $companies->count() . " active companies to check");
            
            // If no companies exist, skip client authentication
            if ($companies->isEmpty()) {
                Log::info("No companies found, skipping client authentication");
                return false;
            }
            
            foreach ($companies as $company) {
                Log::info("Checking company: {$company->company_name} (DB: {$company->database_name})");
                
                // Set client database connection
                $this->setClientConnection($company->database_name);
                
                try {
                    $user = User::on('client')->where('email', $request->email)->first();
                    
                    if (!$user) {
                        Log::info("No user found in company {$company->company_name}");
                        continue;
                    }
                    
                    Log::info("User found in company {$company->company_name} - Active: " . ($user->is_active ? 'Yes' : 'No'));
                    
                    $passwordMatches = Hash::check($request->password, $user->password);
                    Log::info("Password check for company {$company->company_name}: " . ($passwordMatches ? 'Match' : 'No Match'));
                    
                    if ($passwordMatches && $user->is_active) {
                        Auth::login($user, $request->boolean('remember'));
                        $request->session()->regenerate();
                        $request->session()->put('company_id', $company->id);
                        $request->session()->put('company_name', $company->company_name);
                        $request->session()->put('database_name', $company->database_name);
                        
                        Log::info("Client user logged in successfully for company: {$company->company_name}");
                        return true;
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Error checking user in company {$company->id}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info("No matching client user found in any company database");
            
        } catch (\Exception $e) {
            Log::error("Error authenticating client user: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
        
        return false;
    }

    private function setClientConnection($databaseName)
    {
        Log::info("Setting client connection to database: " . $databaseName);
        
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