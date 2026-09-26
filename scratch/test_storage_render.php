<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Admin\SettingsController;

view()->share('errors', new \Illuminate\Support\ViewErrorBag);

$admin = User::where('role', 'admin')->first() ?? User::factory()->make(['role' => 'admin']);
auth()->login($admin);

$controller = app(SettingsController::class);
$view = $controller->storage();

echo "Storage view rendered successfully (" . strlen($view->render()) . " bytes)" . PHP_EOL;
