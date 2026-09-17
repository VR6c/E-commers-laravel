<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckBrokenUnsplashImages extends Command
{
    protected $signature = "images:check-broken-unsplash
                            {--prod : Connect to production database (Neon PostgreSQL)}
                            {--all-urls : Check ALL image URLs not just Unsplash ones}
                            {--export= : Path to export a JSON report of broken URLs}";

    protected $description = "Scan product_images for broken (404/error) Unsplash image URLs";

    private const STATUS_LABELS = [
        200 => "OK",
        301 => "Moved Permanently",
        302 => "Found (redirect)",
        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        429 => "Too Many Requests",
        500 => "Server Error",
        503 => "Service Unavailable",
    ];

    public function handle(): int
    {
        if ($this->option("prod")) {
            config([
                "database.default"                    => "pgsql",
                "database.connections.pgsql.host"     => "ep-frosty-glade-b35p57bv-pooler.c-4.ap-southeast-1.aws.neon.tech",
                "database.connections.pgsql.port"     => "5432",
                "database.connections.pgsql.database" => "api_mobile",
                "database.connections.pgsql.username" => "neondb_owner",
                "database.connections.pgsql.password" => "npg_g0RXtUE5wsWm",
                "database.connections.pgsql.sslmode"  => "require",
            ]);
            DB::purge("pgsql");
            DB::reconnect("pgsql");
            $this->info("Connected to PRODUCTION database (Neon PostgreSQL).");
        } else {
            $this->info("Connected to default database (" . config("database.default") . ").");
        }

        $checkAll   = (bool) $this->option("all-urls");
        $exportPath = $this->option("export");

        $this->info("Fetching image records from product_images...");

        $query = ProductImage::with("product")
            ->whereNotNull("image_url")
            ->where("image_url", "!=", "");

        if (! $checkAll) {
            $query->where(function ($q) {
                $q->where("image_url", "like", "%unsplash.com%")
                  ->orWhere("image_url", "like", "%images.unsplash%");
            });
        }

        $images = $query->get();

        if ($images->isEmpty()) {
            $this->warn("No image records found matching the criteria.");
            return Command::SUCCESS;
        }

        $this->info("Found {$images->count()} image record(s) to check.");
        $this->newLine();

        $broken = [];
        $ok     = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($images->count());
        $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% - %message%");
        $bar->setMessage("starting...");
        $bar->start();

        foreach ($images as $img) {
            $url = $img->image_url;
            $bar->setMessage(Str::limit($url, 55));

            try {
                $response = Http::withHeaders([
                    "User-Agent" => "Mozilla/5.0 (compatible; ImageChecker/1.0)",
                    "Accept"     => "image/*,*/*",
                ])->timeout(10)->head($url);

                $status = $response->status();

                if ($status >= 400 || $status === 0) {
                    $broken[] = [
                        "image_id"     => $img->id,
                        "product_id"   => $img->product_id,
                        "product_name" => optional($img->product)->name ?? "N/A",
                        "type"         => $img->type,
                        "url"          => $url,
                        "status"       => $status,
                        "status_label" => self::STATUS_LABELS[$status] ?? "HTTP {$status}",
                    ];
                    $errors++;
                } else {
                    $ok++;
                }
            } catch (\Throwable $e) {
                $broken[] = [
                    "image_id"     => $img->id,
                    "product_id"   => $img->product_id,
                    "product_name" => optional($img->product)->name ?? "N/A",
                    "type"         => $img->type,
                    "url"          => $url,
                    "status"       => 0,
                    "status_label" => "Connection Error: " . $e->getMessage(),
                ];
                $errors++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $total = $images->count();
        $this->info("Checked: {$total}  |  OK: {$ok}  |  Broken: {$errors}");
        $this->newLine();

        if (empty($broken)) {
            $this->info("All Unsplash image URLs are reachable. No broken links found!");
            return Command::SUCCESS;
        }

        $this->error("Found {$errors} broken image URL(s):");
        $this->newLine();

        $tableRows = array_map(fn($item) => [
            $item["image_id"],
            $item["product_id"],
            Str::limit($item["product_name"], 28),
            $item["type"],
            $item["status"],
            $item["status_label"],
            Str::limit($item["url"], 60),
        ], $broken);

        $this->table(
            ["Img ID", "Prod ID", "Product Name", "Type", "HTTP", "Status", "URL"],
            $tableRows
        );

        $byStatus = collect($broken)->groupBy("status");
        $this->newLine();
        $this->line("Breakdown by HTTP status:");
        foreach ($byStatus as $status => $items) {
            $label = self::STATUS_LABELS[$status] ?? "HTTP {$status}";
            $this->line("  - {$label} ({$status}): {$items->count()} URL(s)");
        }

        if ($exportPath) {
            $json = json_encode([
                "generated_at"  => now()->toIso8601String(),
                "total_checked" => $total,
                "ok_count"      => $ok,
                "broken_count"  => $errors,
                "broken_urls"   => $broken,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            file_put_contents($exportPath, $json);
            $this->newLine();
            $this->info("Broken URL report exported to: {$exportPath}");
        }

        return Command::FAILURE;
    }
}
