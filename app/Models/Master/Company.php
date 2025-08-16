<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'master';
    
    protected $fillable = [
        'company_code', 'company_name', 'email', 'database_name',
        'subscription_plan', 'subscription_status', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_expires_at' => 'datetime',
    ];
}