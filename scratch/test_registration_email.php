<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

echo "Testing Registered event listener resolution...\n";

$listeners = Event::getListeners(Registered::class);
echo "Registered Event Listeners Count: " . count($listeners) . "\n";

$user = User::factory()->make([
    'email' => 'test_verify_instant_' . time() . '@example.com',
    'role' => UserRole::Saler,
]);

echo "Simulating Registered event for seller user...\n";
try {
    event(new Registered($user));
    echo "SUCCESS: Registered event dispatched cleanly!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
