<?php

namespace FQ\Samples;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;

class Simple extends SampleBootstrapper {

	function __construct() {
		parent::__construct('simple');

		$this->addRootDir(new RootDir($this->root . 'root1'));
		$this->addRootDir(new RootDir($this->root . 'root2'));

		$this->addChildDir(new ChildDir('child1'));
		$this->addChildDir(new ChildDir('child2'));
	}

	public function queryFile1FromChild1() {
		return $this->getFilePaths('File1', 'child1');
	}

}