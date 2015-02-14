<?php

namespace FQ\Samples;

use FQ\Files;

class SampleBootstrapper extends Files {

	public $root;

	function __construct($sampleDir) {
		parent::__construct();
		$this->root = __DIR__ . '/' . $sampleDir . '/';
	}

}