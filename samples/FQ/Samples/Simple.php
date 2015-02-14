<?php

namespace FQ\Samples;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;

class Simple extends SampleBootstrapper {

	function __construct() {
		parent::__construct('simple');

		$this->addRootDir(new RootDir('root', $this->root . 'root'));

		$this->addChildDir(new ChildDir('child1'));
		$this->addChildDir(new ChildDir('child2'));
	}

}