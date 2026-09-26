<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\CustomerProfileController;
use Illuminate\Http\Request;

$user = User::first();
auth()->login($user);

echo "Testing profile update for user: " . $user->name . " (Role: " . $user->role->value . ")" . PHP_EOL;
echo "Current designation: " . ($user->profile?->designation ?? 'NONE') . PHP_EOL;

$req = Request::create('/account/profile', 'PATCH', [
    'name' => $user->name,
    'email' => $user->email,
    'phone' => $user->phone,
    'company' => 'Test Company Inc',
    'designation' => 'Lead Architect Test',
    'location' => 'Dhaka, BD',
]);

$controller = app(CustomerProfileController::class);
$controller->update($req);

$user->refresh();
echo "Updated designation: " . ($user->profile?->designation ?? 'NONE') . PHP_EOL;
