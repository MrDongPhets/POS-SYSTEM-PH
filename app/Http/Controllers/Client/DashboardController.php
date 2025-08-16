<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $companyId = session('company_id');
        
        // Get company information
        $company = Company::find($companyId);

        return Inertia::render('client/dashboard', [
            'user' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'company' => [
                'id' => $company->id,
                'code' => $company->company_code,
                'name' => $company->company_name,
            ],
        ]);
    }
}