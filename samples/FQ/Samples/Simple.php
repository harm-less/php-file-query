<?php

namespace FQ\Samples;

use FQ\Collections\Query\QueryCollection;
use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Query\FilesQuery;
use FQ\Query\FilesQueryBuilder;
use FQ\Query\FilesQueryRequirements;

class Simple extends SampleBootstrapper {

	private $_queryCollection;

	function __construct() {
		parent::__construct('simple');

		$this->addRootDir(new RootDir('root1', $this->root() . 'root1'));
		$this->addRootDir(new RootDir('root2', $this->root() . 'root2'));

		$this->addChildDir(new ChildDir('child1', 'child1'));
		$this->addChildDir(new ChildDir('child2', 'child2'));

		$this->_queryCollection = new QueryCollection($this);

		$queryChild1 = new FilesQuery($this);
		$queryChild1ChildSelection = $queryChild1->getChildDirSelection();
		$queryChild1ChildSelection->includeDirById('child1');

		$this->_queryCollection->addQuery('child1', $queryChild1);

		$queryChild2 = new FilesQuery($this);
		$queryChild2ChildSelection = $queryChild2->getChildDirSelection();
		$queryChild2ChildSelection->includeDirById('child2');

		$this->_queryCollection->addQuery('child2', $queryChild2);
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

	public function executeQueryChild1($fileName = 'File1') {
		$query = $this->_queryCollection->getQueryById('child1');
		$query->run($fileName);
		return $query->listPaths();
	}
}