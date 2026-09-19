<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\VerificationDocument;
use App\Http\Controllers\Admin\VerificationController;

$doc = VerificationDocument::latest()->first();
echo "Testing viewDocument for Doc ID: {$doc->id}, path: {$doc->file_path}\n";

$controller = app(VerificationController::class);

try {
    $response = $controller->viewDocument($doc);
    echo "Response class: " . get_class($response) . "\n";
    echo "Redirect URL: " . $response->getTargetUrl() . "\n";
} catch (\Throwable $e) {
    echo "ERROR caught: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
