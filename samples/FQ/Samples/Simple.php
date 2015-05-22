<?php

namespace FQ\Samples;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Query\FilesQueryBuilder;
use FQ\Query\FilesQueryRequirements;

class Simple extends SampleBootstrapper {

	function __construct() {
		parent::__construct('simple');

		$this->addRootDir(new RootDir('root1', $this->root() . 'root1'));
		$this->addRootDir(new RootDir('root2', $this->root() . 'root2'));

		$this->addChildDir(new ChildDir('child1', 'child1'));
		$this->addChildDir(new ChildDir('child2', 'child2'));
	}

	public function queryFile1FromChild1() {
		$builder = new FilesQueryBuilder($this->query());
		return $builder->includeChildDirs('child1')->run('File1')->listPaths();
	}

	public function queryFile1FromRoot1AndFromChild1() {
		$builder = new FilesQueryBuilder($this->query());
		return $builder->includeRootDirs('root1')->includeChildDirs('child1')->run('File1')->listPaths();
	}
	public function queryFile1InReverse() {
		$builder = new FilesQueryBuilder($this->query());
		return $builder->reverse(true)->run('File1')->listPaths();
	}

	public function queryNonExistingFileWithRequirementOne() {
		$builder = new FilesQueryBuilder($this->query());
		return $builder->addRequirement(FilesQueryRequirements::REQUIRE_ONE)->run('File1')->listPaths();
	}

	public function queryNonExistingFileWithRequirementLast() {
		$builder = new FilesQueryBuilder($this->query());
		return $builder->addRequirement(FilesQueryRequirements::REQUIRE_LAST)->run('File1')->listPaths();
	}
	public function queryNonExistingFileWithRequirementAll() {
		$builder = new FilesQueryBuilder($this->query());
		return $builder->addRequirement(FilesQueryRequirements::REQUIRE_ALL)->run('File1')->listPaths();
	}
}