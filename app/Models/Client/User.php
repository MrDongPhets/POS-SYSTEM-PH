<?php

namespace App\Models\Client;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // Dynamic connection will be set based on tenant
    protected $fillable = [
        'store_id', 'employee_code', 'first_name', 'last_name', 
        'email', 'phone', 'password', 'role', 'is_active'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
        'can_override_prices' => 'boolean',
        'can_apply_discounts' => 'boolean',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}