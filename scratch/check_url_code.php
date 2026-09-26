<?php

require __DIR__ . '/../vendor/autoload.php';

$adapter = new \App\Support\CloudinaryAdapter(['cloud_name' => 'w36cggya']);
$flysystem = new \League\Flysystem\Filesystem($adapter);
$lAdapter = new \Illuminate\Filesystem\FilesystemAdapter($flysystem, $adapter, ['cloud_name' => 'https://res.cloudinary.com/w36cggya']);

echo "method_exists getUrl: " . (method_exists($adapter, 'getUrl') ? 'YES' : 'NO') . PHP_EOL;
