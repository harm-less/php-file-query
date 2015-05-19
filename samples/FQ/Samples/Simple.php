<?php

namespace FQ\Samples;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Query\FilesQueryBuilder;

class Simple extends SampleBootstrapper {

	function __construct() {
		parent::__construct('simple');

		$this->addRootDir(new RootDir('root1', $this->root . 'root1'));
		$this->addRootDir(new RootDir('root2', $this->root . 'root2'));

		$this->addChildDir(new ChildDir('child1', 'child1'));
		$this->addChildDir(new ChildDir('child2', 'child2'));
	}

	public function queryFile1FromChild1() {

		//pr($this->queryPath('File2'));
		//var_dump($this->queryPath('does-not'));


		$builder = new FilesQueryBuilder($this);
		$builder->excludeRootDirs('root1')->includeChildDirs('child1')->fileName('File1');

		$query = $builder->run();

		return $query->listPaths();
	}

}