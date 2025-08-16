<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create ENUM types first
        DB::statement("CREATE TYPE user_role AS ENUM ('company_admin', 'store_manager', 'shift_supervisor', 'cashier', 'staff')");
        DB::statement("CREATE TYPE receipt_type AS ENUM ('sales_invoice', 'official_receipt')");
        DB::statement("CREATE TYPE tax_type_enum AS ENUM ('vat', 'vat_exempt', 'zero_rated')");
        DB::statement("CREATE TYPE discount_type_enum AS ENUM ('percentage', 'fixed_amount')");
        DB::statement("CREATE TYPE payment_method_enum AS ENUM ('cash', 'card', 'gcash', 'paymaya', 'bank_transfer')");
        DB::statement("CREATE TYPE transaction_status_enum AS ENUM ('pending', 'completed', 'cancelled', 'voided')");
        DB::statement("CREATE TYPE movement_type_enum AS ENUM ('sale', 'return', 'restock', 'adjustment', 'transfer_in', 'transfer_out', 'damage', 'expired')");
        DB::statement("CREATE TYPE reference_type_enum AS ENUM ('transaction', 'adjustment', 'transfer', 'manual')");

        // Users table (company staff)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('employee_code', 50)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone', 50)->nullable();
            $table->string('password');
            $table->boolean('can_override_prices')->default(false);
            $table->boolean('can_apply_discounts')->default(false);
            $table->boolean('can_process_returns')->default(false);
            $table->boolean('can_void_transactions')->default(false);
            $table->decimal('max_discount_percent', 5, 2)->default(0.00);
            $table->decimal('max_transaction_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('hire_date')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            
            $table->index('email');
            $table->index('is_active');
            $table->index('store_id');
        });

        // Add the ENUM column using raw SQL
        DB::statement('ALTER TABLE users ADD COLUMN role user_role NOT NULL DEFAULT \'staff\'');
        DB::statement('CREATE INDEX idx_users_role ON users(role)');

        // Add default company admin (password: 'password')
        DB::table('users')->insert([
            'first_name' => 'Company',
            'last_name' => 'Administrator',
            'email' => 'admin@company.com',
            'password' => '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
            'role' => 'company_admin',
            'can_override_prices' => true,
            'can_apply_discounts' => true,
            'can_process_returns' => true,
            'can_void_transactions' => true,
            'max_discount_percent' => 50.00,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('users');
        
        // Drop ENUM types
        DB::statement('DROP TYPE IF EXISTS user_role');
        DB::statement('DROP TYPE IF EXISTS receipt_type');
        DB::statement('DROP TYPE IF EXISTS tax_type_enum');
        DB::statement('DROP TYPE IF EXISTS discount_type_enum');
        DB::statement('DROP TYPE IF EXISTS payment_method_enum');
        DB::statement('DROP TYPE IF EXISTS transaction_status_enum');
        DB::statement('DROP TYPE IF EXISTS movement_type_enum');
        DB::statement('DROP TYPE IF EXISTS reference_type_enum');
    }
};