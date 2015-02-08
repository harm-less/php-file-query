<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once __DIR__ . '/setup.php';
$autoloader = require __DIR__ . '/vendor/autoload.php';
$autoloader->add('FQ\\Samples\\', __DIR__);

$sampleSimple = new \FQ\Samples\Simple();
echo $sampleSimple->filePath('File1', 'child1');

?>