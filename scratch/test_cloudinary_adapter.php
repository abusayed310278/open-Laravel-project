<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

Storage::extend('cloudinary', function ($app, array $config) {
    $adapter = new \App\Support\CloudinaryAdapter($config);
    $flysystem = new \League\Flysystem\Filesystem($adapter, $config);

    return new \Illuminate\Filesystem\FilesystemAdapter($flysystem, $adapter, $config);
});

config([
    'filesystems.disks.cloudinary' => [
        'driver' => 'cloudinary',
        'cloud_name' => 'w36cggya',
        'api_key' => '146238954648692',
        'api_secret' => 'BEuXx-vqfq-tPmWMTaRbJd6SyH8',
    ],
]);

try {
    $path = 'openbox-test-file.txt';
    Storage::disk('cloudinary')->put($path, 'Cloudinary connection test — ' . now());
    echo "Upload test successful!" . PHP_EOL;

    Storage::disk('cloudinary')->delete($path);
    echo "Delete test successful!" . PHP_EOL;
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
