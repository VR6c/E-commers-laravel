<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MigratePicsumToImgbbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:migrate-picsum 
                            {--key= : ImgBB API key (defaults to IMGBB_API_KEY env)}
                            {--limit=0 : Max records to migrate (0 = all)}
                            {--dry-run : Preview without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate external picsum.photos product images to ImgBB cloud storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $apiKey = $this->option('key') ?: config('services.imgbb.key') ?: env('IMGBB_API_KEY');

        if (empty($apiKey) && ! $dryRun) {
            $this->error('ImgBB API key is required. Set IMGBB_API_KEY in .env or pass --key=...');
            return Command::FAILURE;
        }

        $query = ProductImage::with('product')
            ->where('image_url', 'like', '%picsum.photos%')
            ->orderBy('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        $records = $query->get();
        $total = $records->count();

        if ($total === 0) {
            $this->info('No picsum.photos URLs found. Nothing to do.');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} picsum.photos image(s) to migrate to ImgBB.");
        if ($dryRun) {
            $this->warn('[DRY RUN - No changes will be saved]');
        }

        $ok = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($records as $record) {
            $url = $record->image_url;
            $productName = $record->product->name ?? 'product-' . $record->product_id;
            $slugName = Str::slug($productName);

            if ($dryRun) {
                $ok++;
                $bar->advance();
                continue;
            }

            try {
                $response = Http::asForm()->timeout(30)->post('https://api.imgbb.com/1/upload', [
                    'key'   => $apiKey,
                    'image' => $url,
                    'name'  => $slugName,
                ]);

                $data = $response->json();

                if ($response->successful() && !empty($data['data']['url'])) {
                    $record->update(['image_url' => $data['data']['url']]);
                    $ok++;
                } else {
                    $failed++;
                    $this->newLine();
                    $this->warn("Failed on Image #{$record->id}: " . ($data['error']['message'] ?? 'Unknown error'));
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("Exception on Image #{$record->id}: " . $e->getMessage());
            }

            usleep(300000); // 0.3s rate-limit protection
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Migration completed! Total: {$total}, Uploaded: {$ok}, Failed: {$failed}");

        return Command::SUCCESS;
    }
}
