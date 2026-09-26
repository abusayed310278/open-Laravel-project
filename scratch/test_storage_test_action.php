<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Http\Controllers\Admin\SettingsController;
use Illuminate\Http\Request;

view()->share('errors', new \Illuminate\Support\ViewErrorBag);

$admin = User::where('role', 'admin')->first() ?? User::factory()->make(['role' => 'admin']);
auth()->login($admin);

$request = Request::create('/admin/settings/storage/test', 'POST', ['disk' => 'cloudinary']);
$controller = app(SettingsController::class);

$response = $controller->testStorage($request);

echo "Test Connection response status: " . $response->getStatusCode() . PHP_EOL;
echo "Redirect session status: " . session('status') . PHP_EOL;
if (session('errors')) {
    var_dump(session('errors')->all());
}
