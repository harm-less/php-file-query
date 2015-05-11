<?php

$root = __DIR__ . '/../';

include_once $root . 'setup.php';

// Load our autoloader, and add our Test class namespace
$autoloader = require($root . 'vendor/autoload.php');
$autoloader->add('FQ\\Tests\\', __DIR__);

// Load our functions bootstrap
require(__DIR__ . '/functions-bootstrap.php');