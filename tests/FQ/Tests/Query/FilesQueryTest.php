<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQuery;
use FQ\Query\FilesQueryRequirements;
use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\RootSelection;

class FilesQueryTest extends AbstractFilesQueryTests {

	function setUp() {
		parent::setUp();

		$this->nonPublicMethodObject($this->query());
	}

	public function testConstructor()
	{
		$files = new FilesQuery($this->_fqApp);
		$this->assertNotNull($files);
		$this->assertTrue($files instanceof FilesQuery);
	}

	public function testReset() {
		$files = $this->query();
		$files->reset();

		$this->assertFalse($files->requirements()->hasRequirements());

		$filters = $files->filters();
		$this->assertEquals(1, count($filters));
		$this->assertEquals(FilesQuery::FILTER_EXISTING, $filters[0]);

		$this->assertNull($files->queriedFileName());
		$this->assertFalse($files->isReversed());
		$this->assertFalse($files->hasRun());
		$this->assertNull($files->queryError());
	}

	public function testResetSelection() {
		$query = $this->query();
		$rootDirSelection = $query->getRootDirSelection();
		$rootDirSelection->includeDirById(self::ROOT_DIR_DEFAULT_ID);

		$childDirSelection = $query->getChildDirSelection();
		$childDirSelection->includeDirById(self::CHILD_DIR_DEFAULT_DIR);

		$this->assertTrue($rootDirSelection->hasIncludedDirsById());
		$this->assertTrue($childDirSelection->hasIncludedDirsById());

		$query->resetSelection();

		$this->assertFalse($rootDirSelection->hasIncludedDirsById());
		$this->assertFalse($childDirSelection->hasIncludedDirsById());
	}

	public function testRequirements() {
		$query = $this->query();
		$requirements = $query->requirements();
		$this->assertNotNull($requirements);
		$this->assertEquals('FQ\Query\FilesQueryRequirements', get_class($requirements));
	}

	public function testReverse() {
		$query = $this->query();
		$this->assertFalse($query->reverse());
		$this->assertTrue($query->reverse(true));
		$this->assertTrue($query->reverse());
	}

	public function testFiltersDefault() {
		$query = $this->query();
		$this->assertEquals(array(FilesQuery::FILTER_EXISTING), $query->filters());
		$this->assertEquals(array(), $query->filters(array(), false));
	}
	public function testFiltersAddDuplicate() {
		$query = $this->query();
		$this->assertEquals(array(FilesQuery::FILTER_EXISTING), $query->filters(array(FilesQuery::FILTER_EXISTING, FilesQuery::FILTER_EXISTING)));
	}
	public function testFiltersAddByString() {
		$query = $this->query();
		$this->assertEquals(array(), $query->filters(array(), false));
		$this->assertEquals(array(FilesQuery::FILTER_EXISTING), $query->filters(FilesQuery::FILTER_EXISTING));
	}

	public function testSetRootDirSelection() {
		$query = $this->query();
		$oldRootDirSelection = $query->getRootDirSelection();
		$newRootDirSelection = new RootSelection();
		$query->setRootDirSelection($newRootDirSelection);
		$this->assertNotEquals(spl_object_hash($query->getRootDirSelection()), spl_object_hash($oldRootDirSelection));
		$this->assertEquals(spl_object_hash($query->getRootDirSelection()), spl_object_hash($newRootDirSelection));
	}

	public function testGetRootDirSelection() {
		$query = $this->query();
		$currentRootDirSelection = $query->getRootDirSelection();
		$this->assertEquals('FQ\Query\Selection\RootSelection', get_class($currentRootDirSelection));

		$newRootDirSelection = $query->getRootDirSelection(true);
		$this->assertNotEquals(spl_object_hash($currentRootDirSelection), spl_object_hash($newRootDirSelection));
	}

	public function testSetChildDirSelection() {
		$query = $this->query();
		$oldChildDirSelection = $query->getChildDirSelection();
		$newChildDirSelection = new ChildSelection();
		$query->setChildDirSelection($newChildDirSelection);
		$this->assertNotEquals(spl_object_hash($query->getChildDirSelection()), spl_object_hash($oldChildDirSelection));
		$this->assertEquals(spl_object_hash($query->getChildDirSelection()), spl_object_hash($newChildDirSelection));
	}

	public function testGetChildDirSelection() {
		$query = $this->query();
		$currentChildDirSelection = $query->getChildDirSelection();
		$this->assertEquals('FQ\Query\Selection\ChildSelection', get_class($currentChildDirSelection));

		$newRootDirSelection = $query->getChildDirSelection(true);
		$this->assertNotEquals(spl_object_hash($currentChildDirSelection), spl_object_hash($newRootDirSelection));
	}

	public function testIsValidChildDir() {
		$query = $this->query();
		$this->assertFalse($query->isValidChildDir($this->_newActualChildDir()));
		$this->assertTrue($query->isValidChildDir($this->childDir()));
	}

	public function testHasRunCheckIfItDidNotRun() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryException', 'You must first call the "run" method before you can retrieve query information');
		$this->callNonPublicMethod('_hasRunCheck', array());
	}
	public function testHasRunCheckIfItDidRun() {
		$this->runQuery();
		$this->assertTrue($this->callNonPublicMethod('_hasRunCheck', array()));
	}

	public function testGetCurrentRootDirSelection() {
		$query = $this->query();

		$this->assertEquals(array($this->rootDir()), $query->getCurrentRootDirSelection());
		$rootDirSelection = $query->getRootDirSelection();
		$rootDirSelection->includeDirById(self::ROOT_DIR_DEFAULT_ID);
		$this->assertEquals(array($this->rootDir()), $query->getCurrentRootDirSelection());

		$rootDirSelection->reset();
		$rootDirSelection->excludeDirById(self::ROOT_DIR_DEFAULT_ID);
		$this->assertEquals(array(), $query->getCurrentRootDirSelection());
	}

	public function testGetCurrentChildDirSelection() {
		$query = $this->query();

		$this->assertEquals(array($this->childDir()), $query->getCurrentChildDirSelection());
		$rootDirSelection = $query->getChildDirSelection();
		$rootDirSelection->includeDirById(self::CHILD_DIR_DEFAULT_ID);
		$this->assertEquals(array($this->childDir()), $query->getCurrentChildDirSelection());

		$rootDirSelection->reset();
		$rootDirSelection->excludeDirById(self::CHILD_DIR_DEFAULT_ID);
		$this->assertEquals(array(), $query->getCurrentChildDirSelection());
	}

	public function testProcessQueryChild() {
		$query = $this->query();
		$childDirs = $query->files()->childDirs();
		$this->assertNotFalse($this->callNonPublicMethod('_processQueryChild', array($childDirs[0], $query->getCurrentRootDirSelection())));
	}
	public function testRunQueryWhenRequirementsAreNotMet() {
		$query = $this->query();
		$query->requirements()->addRequirement(FilesQueryRequirements::REQUIRE_ALL);
		$rootDir = $this->_newFictitiousRootDir(false);
		$this->files()->addRootDir($rootDir);
		$this->runQuery();

		$this->assertNotNull($query->hasQueryError());

		$error = $query->queryError();
		$this->assertNotNull($query->queryError());
	}

	public function testListPathsWhenQueryHasNotRan() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'You must first call the "run" method before you can retrieve query information');
		$this->query()->listPaths();
	}
	public function testListPathsWhenQueryHasRan() {
		$this->runQuery();
		$query = $this->query();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File2.php'
		), $query->listPaths());
	}

	public function testListBasePathsWhenQueryHasNotRan() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'You must first call the "run" method before you can retrieve query information');
		$this->query()->listBasePaths();
	}
	public function testListBasePathsWhenQueryHasRan() {
		$this->runQuery();
		$query = $this->query();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File2.php'
		), $query->listBasePaths());
	}
	public function testListBasePathsWithReversedSetToTrue() {
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$query = $this->query();
		$query->reverse(true);
		$this->runQuery('File1');
		$paths = $query->listBasePaths();
		$index = 0;
		foreach ($paths as $rootDirId => $path) {
			if ($index === 0) {
				$this->assertEquals(self::ROOT_DIR_DEFAULT_ID, $rootDirId);
				$this->assertEquals(self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File1.php', $path);
			}
			else if ($index === 1) {
				$this->assertEquals(self::ROOT_DIR_SECOND_ID, $rootDirId);
				$this->assertEquals(self::ROOT_DIR_SECOND_BASE_PATH . '/child1/File1.php', $path);
			}
			$index++;
		}

		$rawPaths = $query->listRawPaths();
		$index = 0;
		foreach ($paths as $rootDirId => $path) {
			if ($index === 0) {
				$this->assertEquals(self::ROOT_DIR_DEFAULT_ID, $rootDirId);
				$this->assertEquals(self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File1.php', $path);
			}
			else if ($index === 1) {
				$this->assertEquals(self::ROOT_DIR_SECOND_ID, $rootDirId);
				$this->assertEquals(self::ROOT_DIR_SECOND_BASE_PATH . '/child1/File1.php', $path);
			}
			$index++;
		}
	}

	public function testListRawPathsWithReversedSetToTrue() {
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$query = $this->query();
		$query->reverse(true);
		$this->runQuery('File1');
		$rawPaths = $query->listRawPaths();
		$index = 0;
		foreach ($rawPaths as $rootDirId => $path) {
			if ($index === 0) {
				$this->assertEquals(self::ROOT_DIR_DEFAULT_ID, $rootDirId);
				$this->assertEquals(self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php', $path);
			}
			else if ($index === 1) {
				$this->assertEquals(self::ROOT_DIR_SECOND_ID, $rootDirId);
				$this->assertEquals(self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File1.php', $path);
			}
			$index++;
		}
	}

	public function testHasPathsWhenQueryHasNotRan() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'You must first call the "run" method before you can retrieve query information');
		$this->query()->hasPaths();
	}
	public function testHasPathsWhenQueryHasRan() {
		$this->runQuery();
		$query = $this->query();
		$this->assertTrue($query->hasPaths());
	}
}