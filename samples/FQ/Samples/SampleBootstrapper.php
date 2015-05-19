<?php

namespace FQ\Samples;

use FQ\Files;

class SampleBootstrapper extends Files {

	public $_root;

	function __construct($sampleDir) {
		parent::__construct();
		$this->_root = __DIR__ . '\\' . $sampleDir . '\\';
	}

	public function root() {
		return $this->_root;
	}

}