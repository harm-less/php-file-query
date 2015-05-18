<?php

namespace FQ\Tests\Query\Selection;

use FQ\Query\Selection\DirSelection;
use FQ\Tests\Query\AbstractFilesQueryTests;

class DirSelectionTest extends AbstractFilesQueryTests {

	/**
	 * @var DirSelection
	 */
	protected $_dirSelection;

	protected function setUp() {
		parent::setUp();

		$this->_dirSelection = new DirSelection();
		$this->nonPublicMethodObject($this->dirSelection());
	}
	protected function dirSelection() {
		return $this->_dirSelection;
	}

	public function testConstructor() {
		$dirSelection = new DirSelection();
		$this->assertNotNull($dirSelection);
	}

	public function testReset() {
		$selection = $this->dirSelection();
		$selection->reset();

		$this->assertEquals(array(), $selection->getIncludedDirsById());
		$this->assertEquals(array(), $selection->getIncludedDirsByDir());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
		$this->assertEquals(array(), $selection->getExcludedDirsByDir());

		$this->assertFalse($selection->hasIncludedDirsByDir());
		$this->assertFalse($selection->hasIncludedDirsById());
		$this->assertFalse($selection->hasIncludedDirs());
	}

	public function testIncludeDirById() {
		$selection = $this->dirSelection();

		$selection->includeDirById('dir1');
		$selection->includeDirById('dir2');

		$this->assertEquals(array('dir1', 'dir2'), $selection->getIncludedDirsById());
		$this->assertFalse($selection->hasIncludedDirsByDir());
		$this->assertTrue($selection->hasIncludedDirsById());
		$this->assertTrue($selection->hasIncludedDirs());
	}

	public function testExcludeDirById() {
		$selection = $this->dirSelection();

		$selection->excludeDirById('dir1');
		$selection->excludeDirById('dir2');

		$this->assertEquals(array('dir1', 'dir2'), $selection->getExcludedDirsById());
		$this->assertFalse($selection->hasExcludedDirsByDir());
		$this->assertTrue($selection->hasExcludedDirsById());
		$this->assertTrue($selection->hasExcludedDirs());
	}

	public function testIncludeDirByDir() {
		$selection = $this->dirSelection();

		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->includeDir($dir1);
		$selection->includeDir($dir2);

		$this->assertEquals(array($dir1, $dir2), $selection->getIncludedDirsByDir());
		$this->assertTrue($selection->hasIncludedDirsByDir());
		$this->assertFalse($selection->hasIncludedDirsById());
		$this->assertTrue($selection->hasIncludedDirs());
	}

	public function testExcludeDirByDir() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->excludeDir($dir1);
		$selection->excludeDir($dir2);

		$this->assertEquals(array($dir1, $dir2), $selection->getExcludedDirsByDir());
		$this->assertTrue($selection->hasExcludedDirsByDir());
		$this->assertFalse($selection->hasExcludedDirsById());
		$this->assertTrue($selection->hasExcludedDirs());
	}

	public function testIncludeAndExcludeADirAtTheSameTime() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'Cannot exclude a dir when you\'ve already defined included directories');
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->includeDir($dir1);
		$selection->excludeDir($dir2);
	}
	public function testExcludeAndIncludeADirAtTheSameTime() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'Cannot include a dir when you\'ve already defined excluded directories');
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->excludeDir($dir1);
		$selection->includeDir($dir2);
	}

	public function testValidateQuerySelectionWhenDirIsNotAvailable() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'Query selection validation failed because one ore more selection IDs (root1) could not be found in the available directories. Available directory ids are "root2"');
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->includeDir($dir1);
		$selection->validateQuerySelection(array($dir2));
	}
	public function testValidateQuerySelectionWithExcludingWhenDirIsNotAvailable() {
		$this->setExpectedException('FQ\Exceptions\FileQueryException', 'Query selection validation failed because one ore more selection IDs (root1) could not be found in the available directories. Available directory ids are "root2"');
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->excludeDir($dir1);
		$selection->validateQuerySelection(array($dir2));
	}

	public function testGetSelectionWithoutSelection() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$this->assertEquals(array($dir1, $dir2), $selection->getSelection(array($dir1, $dir2)));
	}
	public function testGetSelectionByInclusion() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->includeDir($dir1);
		$this->assertEquals(array($dir1), $selection->getSelection(array($dir1, $dir2)));
	}
	public function testGetSelectionByExclusion() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->excludeDir($dir1);
		$this->assertEquals(array($dir2), $selection->getSelection(array($dir1, $dir2)));
	}
}