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
    protected $signature = 'products:seed {count=100 : Number of products to seed}';

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

        $this->info("Starting to seed {$count} products...");

        $seeder = new BulkProductSeeder();
        $seeded = $seeder->seedProducts($count);

        $this->info("Successfully seeded {$seeded} products with variants and attributes!");

        return Command::SUCCESS;
    }
}
