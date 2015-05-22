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

		$this->_builder = new FilesQueryBuilder($this->query());
		$this->nonPublicMethodObject($this->builder());
	}
	protected function builder() {
		return $this->_builder;
	}

	public function testConstructor()
	{
		$builder = new FilesQueryBuilder($this->query());
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
		$this->assertEquals($builder, $builder->includeRootDirs(array($this->rootDir())));
		$this->assertEquals(array(
			$this->rootDir()
		), $builder->rootSelection()->getIncludedDirsByDir());
	}
	public function testIncludeRootDirsNull() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->includeRootDirs(null));
		$this->assertFalse($builder->rootSelection()->hasIncludedDirs());
	}

	public function testExcludeRootDirsById() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->excludeRootDirs($this->rootDir()->id()));
		$this->assertEquals(array(
			$this->rootDir()->id()
		), $builder->rootSelection()->getExcludedDirsById());
	}
	public function testExcludeRootDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->excludeRootDirs(array($this->rootDir())));
		$this->assertEquals(array(
			$this->rootDir()
		), $builder->rootSelection()->getExcludedDirsByDir());
	}
	public function testExcludeRootDirsNull() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->excludeRootDirs(null));
		$this->assertFalse($builder->rootSelection()->hasExcludedDirs());
	}

	public function testIncludeChildDirsById() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->includeChildDirs($this->childDir()->id()));
		$this->assertEquals(array(
			$this->childDir()->id()
		), $builder->childSelection()->getIncludedDirsById());
	}
	public function testIncludeChildDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->includeChildDirs(array($this->childDir())));
		$this->assertEquals(array(
			$this->childDir()
		), $builder->childSelection()->getIncludedDirsByDir());
	}
	public function testIncludeChildDirsNull() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->includeChildDirs(null));
		$this->assertFalse($builder->childSelection()->hasIncludedDirs());
	}

	public function testExcludeChildDirsById() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->excludeChildDirs($this->childDir()->id()));
		$this->assertEquals(array(
			$this->childDir()->id()
		), $builder->childSelection()->getExcludedDirsById());
	}
	public function testExcludeChildDirsByArrayOfRootDirs() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->excludeChildDirs(array($this->childDir())));
		$this->assertEquals(array(
			$this->childDir()
		), $builder->childSelection()->getExcludedDirsByDir());
	}
	public function testExcludeChildDirsNull() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->excludeChildDirs(null));
		$this->assertFalse($builder->childSelection()->hasExcludedDirs());
	}

	public function testAddDirSelectionByUnknownType() {
		$this->setExpectedException('FQ\Exceptions\FileQueryBuilderException', 'Add type of "non-existing-type" not found.');
		$this->callNonPublicMethod('_addToDirSelection', array('non-existing-type', new DirSelection(), $this->rootDir()));
	}

	public function testAddRequirement() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->addRequirement(FilesQueryRequirements::REQUIRE_ONE));
		$this->assertEquals(array(
			FilesQueryRequirements::REQUIRE_ONE
		), $this->callNonPublicMethod('getRequirements'));
	}

	public function testReverse() {
		$builder = $this->builder();
		$this->assertFalse($this->callNonPublicMethod('isReversed'));

		$builder->reverse(true);
		$this->assertTrue($this->callNonPublicMethod('isReversed'));

		$builder->reverse(false);
		$this->assertFalse($this->callNonPublicMethod('isReversed'));
	}

	public function testShowErrors() {
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->showErrors(true));
	}
	public function testShowErrorsException() {
		$this->setExpectedException('FQ\Exceptions\FileQueryRequirementsException');
		$builder = $this->builder();
		$builder->showErrors(true)->addRequirement(FilesQueryRequirements::REQUIRE_ONE)->run('does-not-exist');
	}

	public function testFilter() {
		$builder = $this->builder();
		$this->assertNull($this->callNonPublicMethod('getFilters'));

		$builder->filters(array());
		$this->assertEquals(array(), $this->callNonPublicMethod('getFilters'));

		$builder->filters(FilesQuery::FILTER_EXISTING);
		$this->assertEquals(FilesQuery::FILTER_EXISTING, $this->callNonPublicMethod('getFilters'));

		$builder->filters(array(FilesQuery::FILTER_EXISTING));
		$this->assertEquals(array(
			FilesQuery::FILTER_EXISTING
		), $this->callNonPublicMethod('getFilters'));
	}

	public function testReset() {
		$builder = $this->builder();

		$this->assertEquals(array(), $builder->rootSelection()->getIncludedDirsByDir());
		$this->assertEquals(array(), $builder->childSelection()->getIncludedDirsByDir());
		$this->assertNull($builder->getFilters());
		$this->assertEquals(array(), $builder->getRequirements());
		$this->assertNull($builder->getFileName());
		$this->assertFalse($builder->isReversed());
		$this->assertTrue($builder->showsErrors());

		$rootDirFirst = $this->_newActualRootDir();
		$rootDirSecond = $this->_newActualRootDirSecond();
		$childDir = $this->_newActualChildDir();
		$builder
			->includeRootDirs(array($rootDirFirst, $rootDirSecond))
			->includeChildDirs($childDir)
			->filters(array(), false)
			->addRequirement(FilesQueryRequirements::REQUIRE_ALL)
			->fileName('Test')
			->reverse(true)
			->showErrors(false);

		$this->assertEquals(array($rootDirFirst, $rootDirSecond), $builder->rootSelection()->getIncludedDirsByDir());
		$this->assertEquals(array($childDir), $builder->childSelection()->getIncludedDirsByDir());
		$this->assertEquals(array(), $builder->getFilters());
		$this->assertEquals(array(FilesQueryRequirements::REQUIRE_ALL), $builder->getRequirements());
		$this->assertEquals('Test', $builder->getFileName());
		$this->assertTrue($builder->isReversed());
		$this->assertFalse($builder->showsErrors());

		$builder->reset();

		$this->assertEquals(array(), $builder->rootSelection()->getIncludedDirsByDir());
		$this->assertEquals(array(), $builder->childSelection()->getIncludedDirsByDir());
		$this->assertNull($builder->getFilters());
		$this->assertEquals(array(), $builder->getRequirements());
		$this->assertNull($builder->getFileName());
		$this->assertFalse($builder->isReversed());
		$this->assertTrue($builder->showsErrors());
	}

	public function testRunBasicBuilderWithoutAFileName() {
		$this->setExpectedException('FQ\Exceptions\FileQueryBuilderException', 'No filename has been set. Use filename() to use a filename for the query or supply it this this run() method');
		$builder = $this->builder();
		$builder->run();
	}
	public function testRunBasicBuilder() {
		$builder = $this->builder();
		$queryInstance = $this->query();
		$query = $builder->run('File2');
		$this->assertEquals($queryInstance, $query);
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File2.php'
		), $query->listPaths());
	}
	public function testRunBasicBuilderWithFilter() {
		$files = $this->files();
		$files->addRootDir($this->_newActualRootDirSecond());
		$builder = $this->builder();
		$this->assertEquals($builder, $builder->filters(FilesQuery::FILTER_EXISTING));
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