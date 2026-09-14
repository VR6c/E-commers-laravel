<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Page;
use App\Models\ProductImage;
use App\Models\Shop;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ImageUploadService;
use Illuminate\Console\Command;

class MigrateImagesToImgbbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate-imgbb 
                            {--table=all : Table to migrate (all, products, banners, categories, brands, shops, pages, site_settings, users, vendors, customers)} 
                            {--limit=0 : Max records to migrate per table (0 = unlimited)} 
                            {--dry-run : Scan candidate images without uploading to ImgBB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate existing local images stored in the database to ImgBB cloud storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $table = $this->option('table');
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $apiKey = config('services.imgbb.key') ?? env('IMGBB_API_KEY');
        if (empty($apiKey) && ! $dryRun) {
            $this->error('IMGBB_API_KEY is not set in your .env or services configuration.');
            return Command::FAILURE;
        }

        $this->info('Starting ImgBB Image Migration' . ($dryRun ? ' (DRY RUN - No changes)' : '') . '...');

        $targets = [
            'products'      => [ProductImage::class, 'image_url', 'products'],
            'banners'       => [Banner::class, 'image_url', 'banner_images'],
            'categories'    => [Category::class, 'image_url', 'categories'],
            'brands'        => [Brand::class, 'logo_url', 'brands'],
            'shops'         => [Shop::class, 'logo', 'shops'],
            'pages'         => [Page::class, 'image_url', 'pages'],
            'site_settings' => [SiteSetting::class, 'logo', 'site_settings'],
            'users'         => [User::class, 'profile_image', 'admin_profiles'],
            'vendors'       => [Vendor::class, 'profile_image', 'vendor_profiles'],
            'customers'     => [Customer::class, 'profile_image', 'customer_profiles'],
        ];

        if ($table !== 'all') {
            if (! isset($targets[$table])) {
                $this->error("Invalid table specified. Allowed: all, " . implode(', ', array_keys($targets)));
                return Command::FAILURE;
            }
            $targets = [$table => $targets[$table]];
        }

        $results = [];

        foreach ($targets as $label => [$modelClass, $column, $folder]) {
            $this->newLine();
            $this->info("Scanning: {$label} ({$modelClass} -> {$column})");

            $query = $modelClass::whereNotNull($column)
                ->where($column, '!=', '')
                ->where($column, 'not like', 'http://%')
                ->where($column, 'not like', 'https://%')
                ->where($column, 'not like', '//%');

            if ($limit > 0) {
                $query->limit($limit);
            }

            $records = $query->get();
            $count = $records->count();

            if ($count === 0) {
                $this->line("  No local images found in {$label}.");
                $results[] = [$label, 0, 0, 0];
                continue;
            }

            $this->line("  Found {$count} local image(s) to process.");

            $migrated = 0;
            $failed = 0;

            if ($dryRun) {
                $results[] = [$label, $count, 0, 0];
                continue;
            }

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            foreach ($records as $record) {
                $currentVal = $record->{$column};
                $localPath = ImageUploadService::resolveLocalPath($currentVal);

                if (! $localPath || ! file_exists($localPath)) {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $newUrl = ImageUploadService::upload($localPath, $folder);

                if (ImageUploadService::isRemoteUrl($newUrl)) {
                    $record->{$column} = $newUrl;
                    $record->saveQuietly();
                    $migrated++;
                } else {
                    $failed++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            $results[] = [$label, $count, $migrated, $failed];
        }

        $this->newLine(2);
        $this->info('Migration Summary:');
        $this->table(
            ['Entity / Table', 'Candidates Found', 'Successfully Migrated', 'Skipped / Failed'],
            $results
        );

        return Command::SUCCESS;
    }
}
