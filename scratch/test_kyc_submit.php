<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\KycService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

$user = User::where('role', 'saler')->first();
if (!$user) {
    echo "No seller found\n";
    exit;
}

echo "Testing KYC submit for user: " . $user->name . " (ID: " . $user->id . ")\n";

// Create a dummy temporary file for UploadedFile
$tmpFilePath = sys_get_temp_dir() . '/test_kyc_' . uniqid() . '.pdf';
file_put_contents($tmpFilePath, 'Dummy PDF Content for KYC verification');

$uploadedFile = new UploadedFile(
    $tmpFilePath,
    'nid_copy.pdf',
    'application/pdf',
    null,
    true
);

$kycService = app(KycService::class);

try {
    $verification = $kycService->submit(
        $user,
        ['nid' => $uploadedFile],
        ['nid' => 'NID-123456789']
    );

    echo "Submission Successful! Verification ID: " . $verification->id . " Status: " . $verification->status->value . "\n";
    foreach ($verification->documents as $doc) {
        $typeStr = is_object($doc->document_type) ? $doc->document_type->value : $doc->document_type;
        $exists = Storage::disk('local')->exists($doc->file_path) ? 'YES' : 'NO';
        echo " - Doc Type: {$typeStr} | Path: {$doc->file_path} | Exists: {$exists}\n";
    }

} catch (\Throwable $e) {
    echo "ERROR caught: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
} finally {
    if (file_exists($tmpFilePath)) {
        @unlink($tmpFilePath);
    }
}
