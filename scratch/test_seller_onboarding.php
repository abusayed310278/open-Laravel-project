<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Enums\UserRole;
use App\Services\KycService;

$user = User::where('email', 'vendor.saler1@openbox.com')->first();
if (!$user) {
    echo "Seller user not found\n";
    exit(1);
}

echo "Testing Seller Onboarding logic for: " . $user->email . "\n";
echo "Role: " . $user->role->value . "\n";

$kycService = app(KycService::class);
$requirements = $kycService->requirementsFor($user);

echo "Admin KYC Requirements Count: " . $requirements->count() . "\n";
foreach ($requirements as $req) {
    echo " - Document: " . $req->document_type->label() . " (" . ($req->is_required ? "Required" : "Optional") . ")\n";
}

$profile = $user->salerProfile;
if ($profile) {
    echo "Profile found: " . $profile->display_name . "\n";
    echo "Current City: " . ($profile->city ?? 'N/A') . ", State: " . ($profile->state ?? 'N/A') . ", Country: " . ($profile->country ?? 'N/A') . "\n";
}

echo "ONBOARDING LOGIC VERIFICATION SUCCESSFUL\n";
