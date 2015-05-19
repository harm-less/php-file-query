<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once __DIR__ . '/setup.php';
$autoloader = require __DIR__ . '/vendor/autoload.php';

$sampleSimple = new \FQ\Samples\Simple();
pr($sampleSimple->queryFile1FromChild1());
pr($sampleSimple->queryFile1FromRoot1AndFromChild1());
pr($sampleSimple->queryFile1InReverse());
pr($sampleSimple->queryNonExistingFileWithRequirementOne());
pr($sampleSimple->queryNonExistingFileWithRequirementLast());
pr($sampleSimple->queryNonExistingFileWithRequirementAll());


function pr($var) {
	$template = php_sapi_name() !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
	printf($template, str_replace(' ', '&nbsp;', print_r($var, true)));
}

?>