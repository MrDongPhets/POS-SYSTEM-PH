<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create custom ENUM types first
        DB::statement("CREATE TYPE system_user_role AS ENUM ('super_admin', 'system_admin', 'support')");
        DB::statement("CREATE TYPE subscription_plan_enum AS ENUM ('trial', 'basic', 'premium', 'enterprise')");
        DB::statement("CREATE TYPE subscription_status_enum AS ENUM ('active', 'suspended', 'expired', 'cancelled')");
        DB::statement("CREATE TYPE database_status_enum AS ENUM ('active', 'suspended', 'migrating', 'inactive')");

        // System Users table
        Schema::create('system_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            
            $table->index('email');
            $table->index('is_active');
        });

        // Add enum column using raw SQL
        DB::statement('ALTER TABLE system_users ADD COLUMN role system_user_role DEFAULT \'system_admin\'');
        DB::statement('CREATE INDEX idx_system_users_role ON system_users(role)');

        // Companies table
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_code', 20)->unique();
            $table->string('company_name');
            $table->string('trade_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('tin', 20)->nullable();
            $table->string('bir_permit_number', 100)->nullable();
            $table->string('dti_permit_number', 100)->nullable();
            $table->string('mayor_permit_number', 100)->nullable();
            $table->string('database_name', 100)->unique();
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_expires_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('max_stores')->default(1);
            $table->integer('max_users')->default(5);
            $table->integer('max_products')->default(1000);
            $table->integer('max_monthly_transactions')->default(1000);
            $table->integer('storage_limit_gb')->default(1);
            $table->string('billing_email')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('onboarding_completed')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('company_code');
            $table->index('database_name');
            $table->index('is_active');
            $table->foreign('created_by')->references('id')->on('system_users')->onDelete('set null');
        });

        // Add enum columns using raw SQL
        DB::statement('ALTER TABLE companies ADD COLUMN database_status database_status_enum DEFAULT \'active\'');
        DB::statement('ALTER TABLE companies ADD COLUMN subscription_plan subscription_plan_enum DEFAULT \'trial\'');
        DB::statement('ALTER TABLE companies ADD COLUMN subscription_status subscription_status_enum DEFAULT \'active\'');
        DB::statement('CREATE INDEX idx_companies_subscription_status ON companies(subscription_status, subscription_expires_at)');

        // Insert default super admin user
        DB::table('system_users')->insert([
            'name' => 'Platform Super Admin',
            'email' => 'admin@possystem.com',
            'password' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => 'super_admin',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('companies');
        Schema::dropIfExists('system_users');
        
        // Drop ENUM types
        DB::statement('DROP TYPE IF EXISTS database_status_enum');
        DB::statement('DROP TYPE IF EXISTS subscription_status_enum');
        DB::statement('DROP TYPE IF EXISTS subscription_plan_enum');
        DB::statement('DROP TYPE IF EXISTS system_user_role');
    }
};