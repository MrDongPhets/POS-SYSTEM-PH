<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Your master schema here (companies, system_users, etc.)
        // Copy the relevant CREATE TABLE statements from your master schema
        
        Schema::create('system_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'system_admin', 'support'])->default('system_admin');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code', 20)->unique();
            $table->string('company_name');
            $table->string('email')->unique();
            $table->string('database_name', 100)->unique();
            // ... add other fields from your schema
            $table->timestamps();
        });
        
        // Add other master tables...
    }

    public function down()
    {
        Schema::dropIfExists('companies');
        Schema::dropIfExists('system_users');
        // Drop other tables...
    }
};