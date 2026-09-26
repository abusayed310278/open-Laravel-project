<?php

require __DIR__ . '/../vendor/autoload.php';

$ref = new ReflectionClass('League\Flysystem\FilesystemAdapter');
foreach ($ref->getMethods() as $m) {
    echo $m->getName() . PHP_EOL;
}
