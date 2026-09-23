<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\User;
use App\Enums\VerificationStatus;
use Illuminate\Http\Request;

$user = User::where('email', 'robb.brakus@example.net')->first();

echo "User found: " . ($user ? $user->name . " ({$user->email})" : "NO") . "\n";

// Simulate Request with ?verification_status=pending_queue
$request = Request::create('/verifier/products', 'GET', [
    'verification_status' => 'pending_queue',
]);

$baseQuery = Product::query();

$query = (clone $baseQuery)
    ->with(['user', 'category', 'brand', 'images', 'gradeAssignment', 'latestVerificationRequest'])
    ->latest();

if ($request->filled('verification_status')) {
    $vStatusVal = $request->query('verification_status');
    if (in_array($vStatusVal, ['pending', 'pending_queue', 'queue'], true)) {
        $query->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
    } else {
        $query->where('verification_status', $vStatusVal);
    }
}

$results = $query->get();
echo "Results count: " . $results->count() . "\n";

foreach ($results as $p) {
    echo "ID: {$p->id} | {$p->title} | vStatus: " . ($p->verification_status->value ?? $p->verification_status) . "\n";
}
