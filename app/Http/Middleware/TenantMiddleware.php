<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Master\Company;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $subdomain = $this->getSubdomain($request);
        
        if ($subdomain && $subdomain !== 'master') {
            $company = Company::where('company_code', $subdomain)->first();
            
            if (!$company || !$company->is_active) {
                abort(404, 'Tenant not found');
            }

            // Set client database connection
            $this->setClientConnection($company->database_name);
        }

        return $next($request);
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