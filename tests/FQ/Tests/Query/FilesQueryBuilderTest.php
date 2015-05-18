<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQuery;
use FQ\Query\FilesQueryBuilder;
use FQ\Query\FilesQueryRequirements;
use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\DirSelection;
use FQ\Query\Selection\RootSelection;

class FilesQueryBuilderTest extends AbstractFilesQueryTests {

	/**
	 * @var FilesQueryBuilder
	 */
	private $_builder;

	function setUp() {
		parent::setUp();

		$this->_builder = new FilesQueryBuilder($this->files());
		$this->nonPublicMethodObject($this->builder());
	}
	protected function builder() {
		return $this->_builder;
	}

	public function testConstructor()
	{
		$builder = new FilesQueryBuilder($this->files());
		$this->assertNotNull($builder);
		$this->assertTrue($builder instanceof FilesQueryBuilder);
	}

	public function testIncludeRootDirsById() {
		$builder = $this->builder();
		$builder->includeRootDirs($this->rootDir()->id());
		$this->assertEquals(array(
			$this->rootDir()->id()
		), $builder->rootSelection()->getIncludedDirsById());
	}
	public function testIncludeRootDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$builder->includeRootDirs(array($this->rootDir()));
		$this->assertEquals(array(
			$this->rootDir()
		), $builder->rootSelection()->getIncludedDirsByDir());
	}

	public function testExcludeRootDirsById() {
		$builder = $this->builder();
		$builder->excludeRootDirs($this->rootDir()->id());
		$this->assertEquals(array(
			$this->rootDir()->id()
		), $builder->rootSelection()->getExcludedDirsById());
	}
	public function testExcludeRootDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$builder->excludeRootDirs(array($this->rootDir()));
		$this->assertEquals(array(
			$this->rootDir()
		), $builder->rootSelection()->getExcludedDirsByDir());
	}

	public function testIncludeChildDirsById() {
		$builder = $this->builder();
		$builder->includeChildDirs($this->childDir()->id());
		$this->assertEquals(array(
			$this->childDir()->id()
		), $builder->childSelection()->getIncludedDirsById());
	}
	public function testIncludeChildDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$builder->includeChildDirs(array($this->childDir()));
		$this->assertEquals(array(
			$this->childDir()
		), $builder->childSelection()->getIncludedDirsByDir());
	}

	public function testExcludeChildDirsById() {
		$builder = $this->builder();
		$builder->excludeChildDirs($this->childDir()->id());
		$this->assertEquals(array(
			$this->childDir()->id()
		), $builder->childSelection()->getExcludedDirsById());
	}
	public function testExcludeChildDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$builder->excludeChildDirs(array($this->childDir()));
		$this->assertEquals(array(
			$this->childDir()
		), $builder->childSelection()->getExcludedDirsByDir());
	}

	public function testAddDirSelectionByUnknownType() {
		$this->setExpectedException('FQ\Exceptions\FileQueryBuilderException', 'Add type of "non-existing-type" not found.');
		$this->callNonPublicMethod('_addToDirSelection', array('non-existing-type', new DirSelection(), $this->rootDir()));
	}

	public function testAddRequirement() {
		$builder = $this->builder();
		$builder->addRequirement(FilesQueryRequirements::LEVELS_ONE);
		$this->assertEquals(array(
			FilesQueryRequirements::LEVELS_ONE
		), $this->callNonPublicMethod('_getRequirements'));
	}

	public function testReverse() {
		$builder = $this->builder();
		$this->assertFalse($this->callNonPublicMethod('_isReversed'));

		$builder->reverse(true);
		$this->assertTrue($this->callNonPublicMethod('_isReversed'));

		$builder->reverse(false);
		$this->assertFalse($this->callNonPublicMethod('_isReversed'));
	}

	public function testFilter() {
		$builder = $this->builder();
		$this->assertNull($this->callNonPublicMethod('_getFilters'));

		$builder->filters(array());
		$this->assertEquals(array(), $this->callNonPublicMethod('_getFilters'));

		$builder->filters(FilesQuery::FILTER_EXISTING);
		$this->assertEquals(FilesQuery::FILTER_EXISTING, $this->callNonPublicMethod('_getFilters'));

		$builder->filters(array(FilesQuery::FILTER_EXISTING));
		$this->assertEquals(array(
			FilesQuery::FILTER_EXISTING
		), $this->callNonPublicMethod('_getFilters'));
	}

	public function testRunBasicBuilderWithoutAFileName() {
		$this->setExpectedException('FQ\Exceptions\FileQueryBuilderException', 'No filename has been set. Use filename() to use a filename for the query or supply it this this run() method');
		$builder = $this->builder();
		$builder->run();
	}
	public function testRunBasicBuilder() {
		$builder = $this->builder();
		$query = $builder->run('File2');
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File2.php'
		), $query->listPaths());
	}
	public function testRunBasicBuilderWithFilter() {
		$files = $this->files();
		$files->addRootDir($this->_newActualRootDirSecond());
		$builder = $this->builder();
		$builder->filters(FilesQuery::FILTER_EXISTING);
		$query = $builder->run('File1');
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File1.php'
		), $query->listPaths());
	}
	public function testRunBasicBuilderWithFileNameProvidedByMethod() {
		$builder = $this->builder();
		$builder->fileName('File1');
		$query = $builder->run();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php'
		), $query->listPaths());
	}
}