<?php
// Migrate picsum.photos product images to ImgBB in production
// ImgBB API accepts a URL directly as the "image" param

$dsn = "pgsql:host=ep-frosty-glade-b35p57bv-pooler.c-4.ap-southeast-1.aws.neon.tech;port=5432;dbname=api_mobile;sslmode=require";
$pdo = new PDO($dsn, "neondb_owner", "npg_g0RXtUE5wsWm");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$IMGBB_KEY = "3cd2fcb26177aa50d6e37f81dec037d3";
$DRY_RUN   = in_array("--dry-run", $argv);

// Fetch all picsum rows
$stmt = $pdo->query(
    "SELECT pi.id, pi.product_id, pi.type, pi.image_url, p.name AS product_name
     FROM product_images pi
     LEFT JOIN products p ON p.id = pi.product_id
     WHERE pi.image_url LIKE '%picsum.photos%'
     ORDER BY pi.id"
);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($rows);

if ($total === 0) {
    echo "No picsum.photos URLs found. Nothing to do." . PHP_EOL;
    exit(0);
}

echo "Found $total picsum.photos image(s) to migrate to ImgBB." . PHP_EOL;
if ($DRY_RUN) echo "[DRY RUN - no changes will be saved]" . PHP_EOL;
echo PHP_EOL;

$ok      = 0;
$failed  = 0;
$results = [];

foreach ($rows as $i => $r) {
    $n    = $i + 1;
    $url  = $r["image_url"];
    $name = preg_replace("/[^a-z0-9\-]/", "-", strtolower($r["product_name"] ?? "product-" . $r["product_id"]));
    $name = trim(preg_replace("/-+/", "-", $name), "-");

    echo "[$n/$total] Uploading: " . substr($url, 0, 70) . " ...";

    if ($DRY_RUN) {
        echo " [skip - dry run]" . PHP_EOL;
        $ok++;
        continue;
    }

    // Upload to ImgBB using URL
    $ch = curl_init("https://api.imgbb.com/1/upload");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_POSTFIELDS     => http_build_query([
            "key"   => $IMGBB_KEY,
            "image" => $url,
            "name"  => $name,
        ]),
    ]);
    $raw  = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo " FAILED (curl: $err)" . PHP_EOL;
        $failed++;
        $results[] = ["id"=>$r["id"], "product_id"=>$r["product_id"], "name"=>$r["product_name"], "old_url"=>$url, "new_url"=>null, "status"=>"FAILED", "error"=>$err];
        usleep(300000);
        continue;
    }

    $data = json_decode($raw, true);
    if (!empty($data["data"]["url"])) {
        $newUrl = $data["data"]["url"];

        // Update DB
        $upd = $pdo->prepare("UPDATE product_images SET image_url = ? WHERE id = ?");
        $upd->execute([$newUrl, $r["id"]]);

        echo " OK -> " . substr($newUrl, 0, 55) . PHP_EOL;
        $ok++;
        $results[] = ["id"=>$r["id"], "product_id"=>$r["product_id"], "name"=>$r["product_name"], "old_url"=>$url, "new_url"=>$newUrl, "status"=>"OK", "error"=>null];
    } else {
        $errMsg = $data["error"]["message"] ?? "Unknown ImgBB error";
        echo " FAILED ($errMsg)" . PHP_EOL;
        $failed++;
        $results[] = ["id"=>$r["id"], "product_id"=>$r["product_id"], "name"=>$r["product_name"], "old_url"=>$url, "new_url"=>null, "status"=>"FAILED", "error"=>$errMsg];
    }

    usleep(300000); // 0.3s rate-limit protection
}

echo PHP_EOL;
echo "=== DONE ===" . PHP_EOL;
echo "Total: $total  |  Uploaded OK: $ok  |  Failed: $failed" . PHP_EOL;

// Export JSON report
$reportPath = __DIR__ . "/picsum_to_imgbb_report.json";
file_put_contents($reportPath, json_encode([
    "generated_at" => date("c"),
    "total"        => $total,
    "ok"           => $ok,
    "failed"       => $failed,
    "dry_run"      => $DRY_RUN,
    "results"      => $results,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "Report saved: $reportPath" . PHP_EOL;
