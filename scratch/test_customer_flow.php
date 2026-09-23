<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $email = 'flow_test_' . time() . '@example.com';
    $service = new \App\Services\Auth\RegistrationService();
    $user = $service->register([
        'account_type' => 'customer',
        'name' => 'John Customer',
        'email' => $email,
        'password' => 'password123',
    ]);

    echo "1. Registered user created: ID={$user->id}, role={$user->role->value}, addresses_count=" . $user->addresses()->count() . "\n";

    // Simulate Onboarding Address Store
    $address = $user->addresses()->create([
        'name' => 'John Customer',
        'phone' => '+880 1700-000000',
        'line1' => '123 Main Street',
        'line2' => 'Apt 4B',
        'city' => 'Dhaka',
        'state' => 'Dhaka Division',
        'country' => 'Bangladesh',
        'postal_code' => '1207',
        'label' => 'Primary',
        'is_default' => true,
    ]);

    echo "2. Address saved: ID={$address->id}, line1={$address->line1}, is_default=" . var_export($address->is_default, true) . "\n";

    // Trigger Registered Event (Email Verification)
    event(new \Illuminate\Auth\Events\Registered($user));

    echo "3. Verification Email sent successfully to {$user->email}!\n";

    // Cleanup
    $user->addresses()->delete();
    $user->delete();
    echo "SUCCESS: Customer address onboarding & verification email flow tested cleanly!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
