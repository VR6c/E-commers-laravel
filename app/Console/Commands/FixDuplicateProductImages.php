<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FixDuplicateProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:fix-duplicate-images 
                            {--prod : Connect directly to production database (Neon PostgreSQL)}
                            {--dry-run : Scan and display duplicates without saving changes}
                            {--all : Update all seeded products with their unique photo rather than only duplicates}
                            {--upload-imgbb : Upload unique photos to ImgBB and save i.ibb.co URLs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds and replaces duplicate product thumbnail images in the database with unique, curated photo URLs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $allMode = (bool) $this->option('all');
        $uploadImgbb = (bool) $this->option('upload-imgbb');

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
            DB::purge('pgsql');
            DB::reconnect('pgsql');
            $this->info('Connected to PRODUCTION database (Neon PostgreSQL).');
        } else {
            $this->info('Connected to default database (' . config('database.default') . ').');
        }

        $mapFile = database_path('seeders/unique_photos_map.json');
        if (!file_exists($mapFile)) {
            $this->error("Unique photos map file not found: {$mapFile}");
            return Command::FAILURE;
        }

        $photoMap = json_decode(file_get_contents($mapFile), true);
        if (empty($photoMap)) {
            $this->error('The photo map is empty or invalid JSON.');
            return Command::FAILURE;
        }

        $this->info('Scanning database for product thumbnail images...');

        // Query all thumb images joined with products
        $images = ProductImage::where('type', 'thumb')
            ->with('product')
            ->get();

        if ($images->isEmpty()) {
            $this->warn('No product thumbnails found in database.');
            return Command::SUCCESS;
        }

        $this->info("Found {$images->count()} total thumbnail records in database.");

        // Group by image_url to identify duplicates
        $urlCounts = $images->groupBy('image_url')->map->count();
        $duplicateUrls = $urlCounts->filter(fn ($count) => $count > 1);

        $this->info("Found {$duplicateUrls->count()} image URLs that are shared by multiple products.");

        $toUpdate = [];
        $usedAssignedUrls = [];

        foreach ($images as $img) {
            $product = $img->product;
            if (!$product) {
                continue;
            }

            $currentUrl = $img->image_url;
            $isDuplicate = isset($duplicateUrls[$currentUrl]);

            if ($allMode || $isDuplicate) {
                $productName = $product->name;

                // Lookup mapped photo ID
                $photoId = $photoMap[$productName] ?? null;

                if (!$photoId) {
                    // Fuzzy match if name has minor differences
                    foreach ($photoMap as $mapName => $id) {
                        if (strcasecmp($mapName, $productName) === 0 || Str::slug($mapName) === Str::slug($productName)) {
                            $photoId = $id;
                            break;
                        }
                    }
                }

                if ($photoId) {
                    $newUrl = "https://images.unsplash.com/photo-{$photoId}?w=600&h=600&fit=crop&auto=format&q=75";

                    // If URL is already distinct and not duplicated, don't needlessly touch unless --all
                    if (!$allMode && $currentUrl === $newUrl && !isset($usedAssignedUrls[$newUrl])) {
                        $usedAssignedUrls[$newUrl] = true;
                        continue;
                    }

                    $usedAssignedUrls[$newUrl] = true;

                    $toUpdate[] = [
                        'image_id'     => $img->id,
                        'product_id'   => $product->id,
                        'product_name' => $productName,
                        'old_url'      => $currentUrl,
                        'new_url'      => $newUrl,
                        'new_name'     => Str::slug($productName) . '.jpg',
                    ];
                }
            }
        }

        $this->info('Candidate records to update: ' . count($toUpdate));

        if (empty($toUpdate)) {
            $this->info('All product thumbnails are already unique! Nothing to update.');
            return Command::SUCCESS;
        }

        // Show sample table
        $sampleRows = array_map(function ($item) {
            return [
                'ID'      => $item['product_id'],
                'Product' => Str::limit($item['product_name'], 30),
                'Old URL' => Str::limit($item['old_url'], 45),
                'New URL' => Str::limit($item['new_url'], 45),
            ];
        }, array_slice($toUpdate, 0, 15));

        $this->newLine();
        $this->table(['ID', 'Product', 'Old Image URL', 'New Image URL'], $sampleRows);
        if (count($toUpdate) > 15) {
            $this->comment('... and ' . (count($toUpdate) - 15) . ' more products.');
        }

        if ($dryRun) {
            $this->newLine();
            $this->warn('[DRY RUN] No database changes were committed. Run without --dry-run to apply.');
            return Command::SUCCESS;
        }

        $this->newLine();
        if (!$this->confirm('Proceed with updating ' . count($toUpdate) . ' product images in the database?', true)) {
            $this->warn('Operation cancelled by user.');
            return Command::SUCCESS;
        }

        $this->info('Applying updates inside transaction...');
        if ($uploadImgbb) {
            $this->warn('Uploading images to ImgBB via API (this will take a few minutes)...');
        }

        DB::beginTransaction();
        try {
            $bar = $this->output->createProgressBar(count($toUpdate));
            $bar->start();

            foreach ($toUpdate as $item) {
                $finalUrl = $item['new_url'];

                if ($uploadImgbb) {
                    try {
                        $res = Http::asForm()->timeout(15)->post('https://api.imgbb.com/1/upload', [
                            'key'   => '3cd2fcb26177aa50d6e37f81dec037d3',
                            'image' => $item['new_url'],
                            'name'  => Str::slug($item['product_name']),
                        ]);

                        if ($res->successful()) {
                            $data = $res->json();
                            if (!empty($data['data']['url'])) {
                                $finalUrl = $data['data']['url'];
                            }
                        }
                    } catch (\Throwable $e) {
                        // Fallback to direct Unsplash CDN URL if ImgBB fails
                    }
                    usleep(250000); // 0.25s rate limit protection
                }

                ProductImage::where('id', $item['image_id'])->update([
                    'image_url' => $finalUrl,
                    'name'      => $item['new_name'],
                ]);
                $bar->advance();
            }

            DB::commit();
            $bar->finish();
            $this->newLine(2);
            $this->info('Successfully updated ' . count($toUpdate) . ' product images!');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->newLine();
            $this->error('Failed to update images: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
