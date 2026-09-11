<?php

namespace App\Console\Commands;

use Database\Seeders\BulkProductSeeder;
use Illuminate\Console\Command;

class SeedProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:seed {count=100 : Number of products to seed} {--prod : Seed directly to production database (Neon PostgreSQL)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed additional products to the database with variants, attributes, and images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');

        if ($count <= 0) {
            $this->error('Please provide a valid count greater than 0.');
            return Command::FAILURE;
        }

        if ($this->option('prod')) {
            config([
                'database.default' => 'pgsql',
                'database.connections.pgsql.host' => 'ep-frosty-glade-b35p57bv-pooler.c-4.ap-southeast-1.aws.neon.tech',
                'database.connections.pgsql.port' => '5432',
                'database.connections.pgsql.database' => 'api_mobile',
                'database.connections.pgsql.username' => 'neondb_owner',
                'database.connections.pgsql.password' => 'npg_g0RXtUE5wsWm',
                'database.connections.pgsql.sslmode' => 'require',
            ]);
            \Illuminate\Support\Facades\DB::purge('pgsql');
            \Illuminate\Support\Facades\DB::reconnect('pgsql');
            $this->info("Connected to PRODUCTION database (Neon PostgreSQL).");
        } else {
            $this->info("Connected to default database (" . config('database.default') . ").");
        }

        \Illuminate\Support\Facades\DB::disableQueryLog();

        $beforeCount = \App\Models\Product::count();
        $this->info("Current products in database: {$beforeCount}");
        $this->info("Starting to seed {$count} products...");

        $bar = $this->output->createProgressBar($count);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $bar->setMessage('Starting...');
        $bar->start();

        $seeder = new BulkProductSeeder();
        $seeded = $seeder->seedProducts($count, function ($current, $total, $productName) use ($bar) {
            $bar->setMessage(substr($productName, 0, 30));
            $bar->advance();
        });

        $bar->finish();
        $this->newLine(2);

        $afterCount = \App\Models\Product::count();
        $this->info("Successfully seeded {$seeded} products with variants and attributes!");
        $this->info("Total products now: {$afterCount} (was {$beforeCount})");

        return Command::SUCCESS;
    }
}
