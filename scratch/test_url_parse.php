<?php

$url = 'cloudinary://146238954648692:BEuXx-vqfq-tPmWMTaRbJd6SyH8@w36cggya';

if (preg_match('#^cloudinary://([^:]+):([^@]+)@(.+)$#i', trim($url), $matches)) {
    echo "API Key: " . $matches[1] . PHP_EOL;
    echo "API Secret: " . $matches[2] . PHP_EOL;
    echo "Cloud Name: " . $matches[3] . PHP_EOL;
} else {
    echo "NO MATCH" . PHP_EOL;
}
