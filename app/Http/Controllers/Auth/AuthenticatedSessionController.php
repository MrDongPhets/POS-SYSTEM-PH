<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Master\Company;
use App\Models\Master\SystemUser;
use App\Models\Client\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Add this line
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
        $host = $request->getHost();
        $subdomain = $this->getSubdomain($request);

        if ($subdomain === 'master' || !$subdomain) {
            // Master/Admin login
            return $this->authenticateSystemUser($request);
        } else {
            // Client/Tenant login
            return $this->authenticateClientUser($request, $subdomain);
        }
    }

    private function authenticateSystemUser(LoginRequest $request)
    {
        if (Auth::guard('master')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('master.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    private function authenticateClientUser(LoginRequest $request, $subdomain)
    {
        $company = Company::where('company_code', $subdomain)->first();
        
        if (!$company || !$company->is_active) {
            return back()->withErrors(['email' => 'Invalid tenant or inactive account.']);
        }

        // Set client database connection
        $this->setClientConnection($company->database_name);
        
        $user = User::on('client')->where('email', $request->email)->first();
        
        if ($user && Hash::check($request->password, $user->password) && $user->is_active) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->put('company_id', $company->id);
            
            return redirect()->intended(route('client.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    private function getSubdomain(Request $request)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        return count($parts) > 2 ? $parts[0] : null;
    }

    private function setClientConnection($databaseName)
    {
        Config::set('database.connections.client', array_merge(
            config('database.connections.client_template'),
            ['database' => $databaseName]
        ));

        DB::purge('client');
        DB::reconnect('client');
    }
}