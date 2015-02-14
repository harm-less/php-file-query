<?php

$root = __DIR__ . '/../';

include_once $root . 'setup.php';

// Load our autoloader, and add our Test class namespace
$autoloader = require($root . 'vendor/autoload.php');
//$autoloader->add('FQ\\Tests\\', __DIR__);
$autoloader->add('FQ\\Samples\\', SAMPLES_ABS);

// Load our functions bootstrap
require(__DIR__ . '/functions-bootstrap.php');