<?php

define('TEST_ROOT', dirname(__DIR__) . '/');

define('ACTUAL_ROOT_DIR_FIRST_ID', 'root1');
define('ACTUAL_ROOT_DIR_FIRST_ABSOLUTE_PATH', TEST_ROOT . 'samples/FQ/Samples/simple/root1');

define('ACTUAL_ROOT_DIR_SECOND_ID', 'root2');
define('ACTUAL_ROOT_DIR_SECOND_ABSOLUTE_PATH', TEST_ROOT . 'samples/FQ/Samples/simple/root2');

define('ACTUAL_CHILD_DIR', 'child1');

include_once TEST_ROOT . 'setup.php';

// Load our autoloader, and add our Test class namespace
$autoloader = require(TEST_ROOT . 'vendor/autoload.php');
$autoloader->add('FQ\\Tests\\', __DIR__);

// Load our functions bootstrap
require(__DIR__ . '/functions-bootstrap.php');