<?php

require __DIR__ . '/../vendor/autoload.php';

$ref = new ReflectionClass('Illuminate\Filesystem\FilesystemAdapter');
$ctor = $ref->getConstructor();
foreach ($ctor->getParameters() as $p) {
    echo $p->getName() . ' : ' . $p->getType() . PHP_EOL;
}
