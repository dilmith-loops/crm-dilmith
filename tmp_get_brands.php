<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$deals = App\Models\Deal::with(['estimates', 'customer'])->get();
foreach($deals as $deal) {
    if (!$deal->contribution) continue;
    $brand = 'Unknown';
    if ($deal->estimates->isNotEmpty()) {
        $firstWithBrand = $deal->estimates->first(fn($e) => !empty($e->brand_name));
        if ($firstWithBrand) {
            $brand = $firstWithBrand->brand_name;
        }
    }
    echo "Deal: {$deal->id}, Estimate Brand: {$brand}, Customer Brand: " . ($deal->customer ? $deal->customer->brand : 'N/A') . ", Contribution: {$deal->contribution}\n";
}
