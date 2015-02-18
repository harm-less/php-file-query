<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once __DIR__ . '/setup.php';
$autoloader = require __DIR__ . '/vendor/autoload.php';
$autoloader->add('FQ\\Samples\\', __DIR__);

$sampleSimple = new \FQ\Samples\Simple();
//print_r($sampleSimple->queryFile1FromChild1());

$builder = new \FQ\Query\FilesQueryBuilder($sampleSimple);
pr($builder->run('File1')->listPaths());


class Foo {
	public function PublicMethod() {}
	private function PrivateMethod() {}
	public static function PublicStaticMethod() {}
	private static function PrivateStaticMethod() {}
}

$foo = new Foo();

$callbacks = array(
	array($foo, 'PublicMethod'),
	array($foo, 'PrivateMethod'),
	array($foo, 'PublicStaticMethod'),
	array($foo, 'PrivateStaticMethod'),
	array('Foo', 'PublicMethod'),
	array('Foo', 'PrivateMethod'),
	array('Foo', 'PublicStaticMethod'),
	array('Foo', 'PrivateStaticMethod'),
);

foreach ($callbacks as $callback) {
	var_dump($callback);
	echo '<br>';
	var_dump(method_exists($callback[0], $callback[1])); // 0: object / class name, 1: method name
	echo '<br>';
	var_dump(is_callable($callback));
	echo '<br>';
	echo str_repeat('-', 40), "\n\n";
	echo '<br>';
	echo '<br>';
}


function pr($var) {
	$template = php_sapi_name() !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
	printf($template, str_replace(' ', '&nbsp;', print_r($var, true)));
}

?>