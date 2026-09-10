<?php

namespace App\Console\Commands;

use App\Services\Shared\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class OptimizeImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize {--quality=82 : WebP quality (1-100)} {--force : Reconvert existing WebP files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compress and convert storefront images to modern WebP format for improved Core Web Vitals';

    /**
     * Execute the console command.
     */
    public function handle(ImageService $imageService): int
    {
        $quality = (int) $this->option('quality');
        $force = (bool) $this->option('force');

        $basePath = storage_path('app/public');
        if (! File::isDirectory($basePath)) {
            $this->error("Directory not found: {$basePath}");
            return Command::FAILURE;
        }

        $this->info("Scanning images in {$basePath}...");

        $directories = ['products', 'banners', 'categories', 'brands', 'banner_images', 'logo_icon'];
        $files = [];

        foreach ($directories as $dir) {
            $dirPath = $basePath . DIRECTORY_SEPARATOR . $dir;
            if (File::isDirectory($dirPath)) {
                $found = File::allFiles($dirPath);
                foreach ($found as $file) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                        $files[] = $file;
                    }
                }
            }
        }

        if (empty($files)) {
            $this->warn('No PNG or JPEG images found to optimize.');
            return Command::SUCCESS;
        }

        $this->info("Found " . count($files) . " candidate images. Starting optimization (Quality: {$quality}%)...");

        $convertedCount = 0;
        $totalOriginalBytes = 0;
        $totalWebpBytes = 0;

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $file) {
            $sourcePath = $file->getRealPath();
            $webpPath = preg_replace('/\.(png|jpe?g)$/i', '.webp', $sourcePath);

            $origSize = filesize($sourcePath);

            if (! $force && file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
                $bar->advance();
                continue;
            }

            $converted = $imageService->convertToWebp($sourcePath, $webpPath, $quality);

            if ($converted && file_exists($webpPath)) {
                $newSize = filesize($webpPath);
                $totalOriginalBytes += $origSize;
                $totalWebpBytes += $newSize;
                $convertedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $savedBytes = $totalOriginalBytes - $totalWebpBytes;
        $savedKb = round($savedBytes / 1024, 1);
        $percent = $totalOriginalBytes > 0 ? round(($savedBytes / $totalOriginalBytes) * 100, 1) : 0;

        $this->info("Optimization complete!");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Images Processed', count($files)],
                ['Images Converted to WebP', $convertedCount],
                ['Original Size', round($totalOriginalBytes / 1024, 1) . ' KB'],
                ['Optimized WebP Size', round($totalWebpBytes / 1024, 1) . ' KB'],
                ['Total Data Saved', "{$savedKb} KB ({$percent}% reduction)"],
            ]
        );

        // Sync to public/storage if symlink exists
        try {
            \Illuminate\Support\Facades\Artisan::call('storage:link');
        } catch (\Throwable $e) {
            // Ignore if already linked
        }

        return Command::SUCCESS;
    }
}
