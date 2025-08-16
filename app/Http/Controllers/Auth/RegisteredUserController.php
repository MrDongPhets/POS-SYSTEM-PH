<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
public function store(Request $request): RedirectResponse
    {
        $host = $request->getHost();
        $subdomain = $this->getSubdomain($request);

        if ($subdomain === 'master' || !$subdomain) {
            // Register system user in master database
            return $this->registerSystemUser($request);
        } else {
            // Register client user in tenant database
            return $this->registerClientUser($request, $subdomain);
        }
    }

    private function registerSystemUser(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:system_users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = \App\Models\Master\SystemUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'system_admin',
            'is_active' => true,
        ]);

        event(new Registered($user));
        Auth::guard('master')->login($user);

        return redirect()->intended(route('master.dashboard', absolute: false));
    }

    private function registerClientUser(Request $request, $subdomain): RedirectResponse
    {
        $company = \App\Models\Master\Company::where('company_code', $subdomain)->first();
        
        if (!$company || !$company->is_active) {
            return back()->withErrors(['email' => 'Invalid tenant or inactive account.']);
        }

        // Set client database connection
        $this->setClientConnection($company->database_name);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Split name into first_name and last_name
        $nameParts = explode(' ', $request->name, 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

        $user = \App\Models\Client\User::on('client')->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'company_admin',
            'is_active' => true,
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->put('company_id', $company->id);

        return redirect()->intended(route('client.dashboard', absolute: false));
    }

    private function getSubdomain(Request $request)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        return count($parts) > 2 ? $parts[0] : null;
    }

    private function setClientConnection($databaseName)
    {
        \Illuminate\Support\Facades\Config::set('database.connections.client', array_merge(
            config('database.connections.client_template'),
            ['database' => $databaseName]
        ));

        \Illuminate\Support\Facades\DB::purge('client');
        \Illuminate\Support\Facades\DB::reconnect('client');
    }
}
