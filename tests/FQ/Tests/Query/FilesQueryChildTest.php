<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQuery;
use FQ\Query\FilesQueryChild;

class FilesQueryChildTest extends AbstractFilesQueryTests {


	protected function setUp() {
		parent::setUp();

		$this->nonPublicMethodObject($this->queryChild());
	}

	public function testConstructor()
	{
		$filesQueryChild = new FilesQueryChild($this->query(), $this->_newActualChildDir());
		$this->assertNotNull($filesQueryChild);
	}

	public function testReset() {
		$queryChild = $this->queryChild();
		$queryChild->reset();
	}

	public function testSetRootDirs() {
		$firstRootDir = $this->_newActualRootDir();
		$secondRootDir = $this->_newActualRootDirSecond();
		$queryChild = $this->queryChild();
		$queryChild->setRootDirs(array($firstRootDir, $secondRootDir));
		$this->assertEquals(array($firstRootDir, $secondRootDir), $queryChild->getRootDirs());
	}

	public function testGetRootDirsEmpty() {
		$queryChild = $this->queryChild();
		$queryChild->setRootDirs(null);
		$this->assertEquals(array(), $queryChild->getRootDirs());
	}

	public function testQuery() {
		$queryChild = $this->queryChild();
		$this->assertNotNull($queryChild->query());
		$this->assertTrue(is_a($queryChild->query(), 'FQ\Query\FilesQuery'));
	}

	public function testFiles() {
		$queryChild = $this->queryChild();
		$this->assertNotNull($queryChild->files());
		$this->assertTrue(is_a($queryChild->files(), 'FQ\Files'));
	}

	public function testChildDir() {
		$queryChild = $this->queryChild();
		$this->assertNotNull($queryChild->childDir());
		$this->assertTrue(is_a($queryChild->childDir(), 'FQ\Dirs\ChildDir'));
	}

	public function testRelativePath() {
		$queryChild = $this->queryChild();
		$this->runQuery();
		$this->assertEquals('/child1/File2.php', $queryChild->relativePath());
	}
	public function testRelativePathWithCustomExtension() {
		$queryChild = $this->queryChild();
		$this->runQuery('File2.ext');
		$this->assertEquals('/child1/File2.ext', $queryChild->relativePath());
	}
	public function testRawAbsolutePath() {
		$queryChild = $this->queryChild();
		$this->runQuery();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File2.php'
		), $queryChild->rawAbsolutePaths());
	}
	public function testBaseAbsolutePath() {
		$queryChild = $this->queryChild();
		$this->runQuery();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File2.php'
		), $queryChild->rawBasePaths());
	}

	public function testGeneratePaths() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryException', 'Cannot generate paths because method (non-existing-dir-method) is not defined in FQ\Dirs\RootDir');
		$this->runQuery();
		$this->callNonPublicMethod('_generatePaths', array('non-existing-dir-method'));
	}

	public function testFilteredAbsolutePathsWithoutFilters() {
		$this->query()->filters(array(), false);
		$queryChild = $this->queryChild();
		$firstRootDir = $this->_newActualRootDir();
		$secondRootDir = $this->_newActualRootDirSecond();
		$queryChild->setRootDirs(array($firstRootDir, $secondRootDir));
		$this->runQuery();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File2.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File2.php'
		), $queryChild->filteredAbsolutePaths());
	}
	public function testFilteredAbsolutePathsWithOneNonExistingFile() {
		$query = $this->query();
		$queryChild = $this->queryChild();
		$query->filters(FilesQuery::FILTER_EXISTING);
		$firstRootDir = $this->_newActualRootDir();
		$secondRootDir = $this->_newActualRootDirSecond();
		$thirdFictitiousDir = $this->_newFictitiousRootDir();
		$queryChild->setRootDirs(array($firstRootDir, $secondRootDir, $thirdFictitiousDir));
		$this->runQuery('File1');
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File1.php',
			self::ROOT_DIR_FICTITIOUS_ID => self::ROOT_DIR_FICTITIOUS_ABSOLUTE_PATH . '/child1/File1.php'
		), $queryChild->rawAbsolutePaths());
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File1.php'
		), $queryChild->filteredAbsolutePaths());
	}

	public function testFilteredBasePathsWithoutFilters() {
		$this->query()->filters(array(), false);
		$queryChild = $this->queryChild();
		$firstRootDir = $this->_newActualRootDir();
		$secondRootDir = $this->_newActualRootDirSecond();
		$queryChild->setRootDirs(array($firstRootDir, $secondRootDir));
		$this->runQuery();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File2.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_BASE_PATH . '/child1/File2.php'
		), $queryChild->filteredBasePaths());
	}
	public function testFilteredBasePathsWithOneNonExistingFile() {
		$query = $this->query();
		$queryChild = $this->queryChild();
		$query->filters(FilesQuery::FILTER_EXISTING);
		$firstRootDir = $this->_newActualRootDir();
		$secondRootDir = $this->_newActualRootDirSecond();
		$thirdFictitiousDir = $this->_newFictitiousRootDir();
		$queryChild->setRootDirs(array($firstRootDir, $secondRootDir, $thirdFictitiousDir));
		$this->runQuery('File1');
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File1.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_BASE_PATH . '/child1/File1.php',
			self::ROOT_DIR_FICTITIOUS_ID => self::ROOT_DIR_FICTITIOUS_BASE_PATH . '/child1/File1.php'
		), $queryChild->rawBasePaths());
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File1.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_BASE_PATH . '/child1/File1.php'
		), $queryChild->filteredBasePaths());
	}

	public function testPathsExist() {
		$queryChild = $this->queryChild();
		$this->runQuery();
		foreach ($queryChild->filteredAbsolutePaths() as $path) {
			$this->assertFileExists($path);
		}
	}

	public function testTotalExistingPaths() {
		$queryChild = $this->queryChild();
		$this->runQuery();
		$this->assertEquals(1, $queryChild->totalExistingPaths());
	}

	public function testResetAfterQuery() {
		$queryChild = $this->queryChild();
		$this->runQuery();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File2.php'
		), $queryChild->rawBasePaths());
		$queryChild->reset();
		$this->assertEquals(array(), $queryChild->rawBasePaths());
		$queryChild->setRootDirs(array($this->_newActualRootDir()));
		$this->runQuery();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_BASE_PATH . '/child1/File2.php'
		), $queryChild->rawBasePaths());
	}
}