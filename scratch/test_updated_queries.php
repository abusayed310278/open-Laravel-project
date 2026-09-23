<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVerification;
use App\Enums\VerificationStatus;
use Illuminate\Http\Request;

echo "--- Testing Verifier ProductController ---\n";
foreach (['pending_queue', 'pending', 'scheduled', 'inspecting'] as $vStatus) {
    $req = Request::create('/verifier/products', 'GET', ['verification_status' => $vStatus]);
    $q = Product::query()->latest();
    if ($req->filled('verification_status')) {
        $vStatusVal = $req->query('verification_status');
        if (in_array($vStatusVal, ['pending', 'pending_queue', 'queue', 'scheduled', 'inspecting'], true)) {
            $q->whereIn('verification_status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
        } else {
            $q->where('verification_status', $vStatusVal);
        }
    }
    echo "Param '{$vStatus}' => Result Count: " . $q->count() . "\n";
}

echo "\n--- Testing AppointmentController Index ---\n";
foreach (['robb.brakus@example.net', 'verifier@openbox.com', 'verifier2@openbox.com'] as $email) {
    $user = User::where('email', $email)->first();
    $aq = ProductVerification::query()
        ->with(['product', 'seller', 'location'])
        ->whereIn('status', [VerificationStatus::Scheduled, VerificationStatus::Inspecting, VerificationStatus::Pending]);
    
    echo "User {$user->name} ({$email}) => Total Pending Queue Count: " . $aq->count() . "\n";
}
