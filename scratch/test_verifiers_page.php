<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Enums\UserRole;

$admin = User::where('role', UserRole::Admin)->first();
auth()->login($admin);

$verifiers = User::where('role', UserRole::Verifier)->get();
echo "Verifiers count: " . $verifiers->count() . PHP_EOL;

foreach ($verifiers as $v) {
    $url = route('admin.verifiers.assign-location', $v);
    echo "Verifier ID: {$v->id} ({$v->name}) -> assign-location route: {$url}" . PHP_EOL;
}

try {
    $html = view('admin.verifiers.index', [
        'verifiers' => User::query()
            ->where('role', UserRole::Verifier)
            ->with('verifierProfile.location')
            ->orderBy('name')
            ->simplePaginate(15),
        'locations' => \App\Models\VerificationLocation::query()->orderBy('name')->get(),
    ])->render();
    echo "View rendered successfully! Length: " . strlen($html) . PHP_EOL;
} catch (\Throwable $e) {
    echo "View render ERROR: " . $e->getMessage() . PHP_EOL;
}
