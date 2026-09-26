<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Http\Request;

$admin = User::where('role', 'admin')->first() ?? User::factory()->make(['role' => 'admin']);
auth()->login($admin);

$request = Request::create('/admin/settings/storage/test', 'POST', ['disk' => 'cloudinary']);
$request->headers->set('Accept', 'application/json');

$controller = app(SettingsController::class);
$response = $controller->testStorage($request);

echo "JSON Response status: " . $response->getStatusCode() . PHP_EOL;
echo "JSON Response content: " . $response->getContent() . PHP_EOL;
