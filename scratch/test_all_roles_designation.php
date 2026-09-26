<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Enums\UserRole;
use App\Http\Controllers\CustomerProfileController;
use Illuminate\Http\Request;

$roles = [
    UserRole::Admin,
    UserRole::Verifier,
    UserRole::Business,
    UserRole::Saler,
    UserRole::Customer,
];

$controller = app(CustomerProfileController::class);

foreach ($roles as $role) {
    $user = User::where('role', $role)->first();
    if (!$user) {
        $user = User::factory()->create([
            'name' => 'Test ' . $role->label(),
            'email' => 'test_' . $role->value . '@example.com',
            'role' => $role,
        ]);
    }

    auth()->login($user);

    $newDesignation = 'Senior ' . $role->label() . ' Specialist';

    $req = Request::create('/account/profile', 'PATCH', [
        'name' => $user->name,
        'email' => $user->email,
        'phone' => $user->phone ?? '+123456789',
        'company' => 'Test Company',
        'designation' => $newDesignation,
        'location' => 'Dubai, UAE',
    ]);

    $controller->update($req);

    $user->refresh();
    $savedDesignation = $user->profile?->designation;

    if ($savedDesignation === $newDesignation) {
        echo "[SUCCESS] Role: {$role->label()} -> Saved Designation: '{$savedDesignation}'" . PHP_EOL;
    } else {
        echo "[FAILED] Role: {$role->label()} -> Expected: '{$newDesignation}', Got: '{$savedDesignation}'" . PHP_EOL;
    }
}
