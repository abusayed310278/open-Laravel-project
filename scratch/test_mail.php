<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config([
    'mail.default' => 'smtp',
    'mail.mailers.smtp.host' => 'smtp.gmail.com',
    'mail.mailers.smtp.port' => 587,
    'mail.mailers.smtp.encryption' => 'tls',
    'mail.mailers.smtp.username' => 'utsab@duet.ac.bd',
    'mail.mailers.smtp.password' => 'lcoi wsup axlf ihxw',
    'mail.from.address' => 'utsab@duet.ac.bd',
    'mail.from.name' => 'OpenBox',
]);

try {
    Illuminate\Support\Facades\Mail::raw('Test email from OpenBox verification fix', function ($message) {
        $message->to('utsab@duet.ac.bd')->subject('OpenBox Test Verification Email');
    });
    echo "SUCCESS: Email sent via Gmail SMTP!\n";
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
