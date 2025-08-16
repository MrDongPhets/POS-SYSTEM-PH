<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class MigrateClientDatabase extends Command
{
    protected $signature = 'migrate:client {database_name}';
    protected $description = 'Migrate a specific client database';

    public function handle()
    {
        $databaseName = $this->argument('database_name');
        
        // Configure client connection
        Config::set('database.connections.client.database', $databaseName);
        DB::purge('client');
        DB::reconnect('client');
        
        $this->info("Migrating client database: {$databaseName}");
        
        try {
            // Run client migrations
            Artisan::call('migrate', [
                '--database' => 'client',
                '--path' => 'database/migrations/client'
            ]);
            
            $this->info("Successfully migrated client database: {$databaseName}");
            
        } catch (\Exception $e) {
            $this->error("Failed to migrate client database: " . $e->getMessage());
        }
    }
}