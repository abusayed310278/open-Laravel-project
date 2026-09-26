<?php

require __DIR__ . '/../vendor/autoload.php';

Cloudinary::config([
    'cloud_name' => 'demo_cloud',
    'api_key' => '123456',
    'api_secret' => 'secret_123',
    'secure' => true,
]);

$options = ['secure' => true];
$url = Cloudinary::cloudinary_url('sample.jpg', $options);
echo "Cloudinary URL: " . $url . PHP_EOL;
