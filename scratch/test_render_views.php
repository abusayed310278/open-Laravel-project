<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;

echo "--- Testing Admin Views Rendering ---" . PHP_EOL;

$admin = User::where('role', UserRole::Admin)->first() ?? User::factory()->create(['role' => UserRole::Admin]);
Auth::login($admin);
view()->share('errors', new ViewErrorBag);

try {
    $dashView = view('admin.dashboard', app(\App\Http\Controllers\Admin\DashboardController::class)->index()->getData())->render();
    echo "1. Admin Dashboard View: RENDER SUCCESS (" . strlen($dashView) . " bytes)" . PHP_EOL;
} catch (\Throwable $e) {
    echo "1. Admin Dashboard View: ERROR - " . $e->getMessage() . PHP_EOL;
}

try {
    $kycView = view('admin.verifications.index', app(\App\Http\Controllers\Admin\VerificationController::class)->index(new \Illuminate\Http\Request)->getData())->render();
    echo "2. Admin KYC Verifications Index View: RENDER SUCCESS (" . strlen($kycView) . " bytes)" . PHP_EOL;
} catch (\Throwable $e) {
    echo "2. Admin KYC Verifications Index View: ERROR - " . $e->getMessage() . PHP_EOL;
}

try {
    $verif = UserVerification::first() ?? UserVerification::create(['user_id' => $admin->id, 'status' => \App\Enums\KycStatus::Draft]);
    $showView = view('admin.verifications.show', app(\App\Http\Controllers\Admin\VerificationController::class)->show($verif)->getData())->render();
    echo "3. Admin KYC Verification Show View: RENDER SUCCESS (" . strlen($showView) . " bytes)" . PHP_EOL;
} catch (\Throwable $e) {
    echo "3. Admin KYC Verification Show View: ERROR - " . $e->getMessage() . PHP_EOL;
}

echo "--- All render tests complete ---" . PHP_EOL;
