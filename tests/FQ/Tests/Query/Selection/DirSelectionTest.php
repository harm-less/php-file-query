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

		$this->assertTrue($selection->includeDirById('dir1'));
		$this->assertTrue($selection->includeDirById('dir2'));

		$this->assertEquals(array('dir1', 'dir2'), $selection->getIncludedDirsById());
		$this->assertFalse($selection->hasIncludedDirsByDir());
		$this->assertTrue($selection->hasIncludedDirsById());
		$this->assertTrue($selection->hasIncludedDirs());

		$selection->lock();
		$this->assertFalse($selection->includeDirById('dir3'));
		$this->assertEquals(array('dir1', 'dir2'), $selection->getIncludedDirsById());

		$selection->unlock();
		$this->assertTrue($selection->includeDirById('dir3'));
		$this->assertEquals(array('dir1', 'dir2', 'dir3'), $selection->getIncludedDirsById());
	}
	public function testRemoveIncludedDirById() {
		$selection = $this->dirSelection();

		$selection->includeDirById('dir1');
		$selection->includeDirById('dir2');

		$this->assertTrue($selection->removeIncludedDirById('dir1'));
		$this->assertFalse($selection->removeIncludedDirById('dir1'));
		$this->assertEquals(array('dir2'), $selection->getIncludedDirsById());

		$selection->lock();
		$this->assertNull($selection->removeIncludedDirById('dir2'));
		$this->assertEquals(array('dir2'), $selection->getIncludedDirsById());

		$selection->unlock();
		$this->assertTrue($selection->removeIncludedDirById('dir2'));
		$this->assertEquals(array(), $selection->getIncludedDirsById());
	}
	public function testRemoveAllIncludedDirById() {
		$selection = $this->dirSelection();

		$selection->includeDirById('dir1');
		$selection->includeDirById('dir2');

		$selection->lock();
		$this->assertFalse($selection->removeAllIncludedDirsById());
		$this->assertEquals(array('dir1', 'dir2'), $selection->getIncludedDirsById());

		$selection->unlock();
		$this->assertTrue($selection->removeAllIncludedDirsById());
		$this->assertEquals(array(), $selection->getIncludedDirsById());
	}

	public function testExcludeDirById() {
		$selection = $this->dirSelection();

		$this->assertTrue($selection->excludeDirById('dir1'));
		$this->assertTrue($selection->excludeDirById('dir2'));

		$this->assertEquals(array('dir1', 'dir2'), $selection->getExcludedDirsById());
		$this->assertFalse($selection->hasExcludedDirsByDir());
		$this->assertTrue($selection->hasExcludedDirsById());
		$this->assertTrue($selection->hasExcludedDirs());

		$selection->lock();
		$this->assertFalse($selection->excludeDirById('dir3'));
		$this->assertEquals(array('dir1', 'dir2'), $selection->getExcludedDirsById());

		$selection->unlock();
		$this->assertTrue($selection->excludeDirById('dir3'));
		$this->assertEquals(array('dir1', 'dir2', 'dir3'), $selection->getExcludedDirsById());
	}
	public function testRemoveExcludedDirById() {
		$selection = $this->dirSelection();

		$selection->excludeDirById('dir1');
		$selection->excludeDirById('dir2');

		$this->assertTrue($selection->removeExcludedDirById('dir1'));
		$this->assertFalse($selection->removeExcludedDirById('dir1'));
		$this->assertEquals(array('dir2'), $selection->getExcludedDirsById());

		$selection->lock();
		$this->assertNull($selection->removeExcludedDirById('dir2'));
		$this->assertEquals(array('dir2'), $selection->getExcludedDirsById());

		$selection->unlock();
		$this->assertTrue($selection->removeExcludedDirById('dir2'));
		$this->assertEquals(array(), $selection->getExcludedDirsById());
	}
	public function testRemoveAllExcludedDirById() {
		$selection = $this->dirSelection();

		$selection->excludeDirById('dir1');
		$selection->excludeDirById('dir2');

		$selection->lock();
		$this->assertFalse($selection->removeAllExcludedDirsById());
		$this->assertEquals(array('dir1', 'dir2'), $selection->getExcludedDirsById());

		$selection->unlock();
		$this->assertTrue($selection->removeAllExcludedDirsById());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
	}

	public function testIncludeDirByDir() {
		$selection = $this->dirSelection();

		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$dir3 = $this->_newFictitiousRootDir();
		$selection->includeDir($dir1);
		$selection->includeDir($dir2);

		$this->assertEquals(array($dir1, $dir2), $selection->getIncludedDirsByDir());
		$this->assertTrue($selection->hasIncludedDirsByDir());
		$this->assertFalse($selection->hasIncludedDirsById());
		$this->assertTrue($selection->hasIncludedDirs());

		$selection->lock();
		$this->assertFalse($selection->includeDir($dir3));
		$this->assertEquals(array($dir1, $dir2), $selection->getIncludedDirsByDir());

		$selection->unlock();
		$this->assertTrue($selection->includeDir($dir3));
		$this->assertEquals(array($dir1, $dir2, $dir3), $selection->getIncludedDirsByDir());
	}
	public function testRemoveIncludedDir() {
		$selection = $this->dirSelection();

		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();

		$selection->includeDir($dir1);
		$selection->includeDir($dir2);

		$this->assertTrue($selection->removeIncludedDir($dir1));
		$this->assertFalse($selection->removeIncludedDir($dir1));
		$this->assertEquals(array($dir2), $selection->getIncludedDirsByDir());

		$selection->lock();
		$this->assertNull($selection->removeIncludedDir($dir2));
		$this->assertEquals(array($dir2), $selection->getIncludedDirsByDir());

		$selection->unlock();
		$this->assertTrue($selection->removeIncludedDir($dir2));
		$this->assertEquals(array(), $selection->getIncludedDirsById());
	}
	public function testRemoveAllIncludedDir() {
		$selection = $this->dirSelection();

		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();

		$selection->includeDir($dir1);
		$selection->includeDir($dir2);

		$selection->lock();
		$this->assertFalse($selection->removeAllIncludedDirs());
		$this->assertEquals(array($dir1, $dir2), $selection->getIncludedDirsByDir());

		$selection->unlock();
		$this->assertTrue($selection->removeAllIncludedDirs());
		$this->assertEquals(array(), $selection->getIncludedDirsByDir());
	}

	public function testExcludeDirByDir() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$dir3 = $this->_newFictitiousRootDir();
		$selection->excludeDir($dir1);
		$selection->excludeDir($dir2);

		$this->assertEquals(array($dir1, $dir2), $selection->getExcludedDirsByDir());
		$this->assertTrue($selection->hasExcludedDirsByDir());
		$this->assertFalse($selection->hasExcludedDirsById());
		$this->assertTrue($selection->hasExcludedDirs());

		$selection->lock();
		$this->assertFalse($selection->excludeDir($dir3));
		$this->assertEquals(array($dir1, $dir2), $selection->getExcludedDirsByDir());

		$selection->unlock();
		$this->assertTrue($selection->excludeDir($dir3));
		$this->assertEquals(array($dir1, $dir2, $dir3), $selection->getExcludedDirsByDir());
	}
	public function testRemoveExcludedDir() {
		$selection = $this->dirSelection();

		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();

		$selection->excludeDir($dir1);
		$selection->excludeDir($dir2);

		$this->assertTrue($selection->removeExcludedDir($dir1));
		$this->assertFalse($selection->removeExcludedDir($dir1));
		$this->assertEquals(array($dir2), $selection->getExcludedDirsByDir());

		$selection->lock();
		$this->assertNull($selection->removeExcludedDir($dir2));
		$this->assertEquals(array($dir2), $selection->getExcludedDirsByDir());

		$selection->unlock();
		$this->assertTrue($selection->removeExcludedDir($dir2));
		$this->assertEquals(array(), $selection->getExcludedDirsById());
	}
	public function testRemoveAllExcludedDir() {
		$selection = $this->dirSelection();

		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();

		$selection->excludeDir($dir1);
		$selection->excludeDir($dir2);

		$selection->lock();
		$this->assertFalse($selection->removeAllExcludedDirs());
		$this->assertEquals(array($dir1, $dir2), $selection->getExcludedDirsByDir());

		$selection->unlock();
		$this->assertTrue($selection->removeAllExcludedDirs());
		$this->assertEquals(array(), $selection->getExcludedDirsByDir());
	}

	public function testUnsafeImport() {
		$selection = $this->dirSelection();

		$selection->unsafeImport(null, null, null, null);

		$this->assertEquals(array(), $selection->getIncludedDirsById());
		$this->assertEquals(array(), $selection->getIncludedDirsByDir());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
		$this->assertEquals(array(), $selection->getExcludedDirsByDir());

		$rootDir1 = $this->_newActualRootDir();
		$rootDir2 = $this->_newActualRootDirSecond();

		// include
		$selection->unsafeImport(null, array($rootDir1), null, null);
		$this->assertEquals(array(), $selection->getIncludedDirsById());
		$this->assertEquals(array($rootDir1), $selection->getIncludedDirsByDir());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
		$this->assertEquals(array(), $selection->getExcludedDirsByDir());
		$this->assertEquals(array($rootDir1), $selection->getSelection(array($rootDir1, $rootDir2)));

		$selection->removeAllIncludedDirs();

		// exclude
		$selection->unsafeImport(null, null, null, array($rootDir1));
		$this->assertEquals(array(), $selection->getIncludedDirsById());
		$this->assertEquals(array(), $selection->getIncludedDirsByDir());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
		$this->assertEquals(array($rootDir1), $selection->getExcludedDirsByDir());
		$this->assertEquals(array($rootDir2), $selection->getSelection(array($rootDir1, $rootDir2)));
	}

	public function testCopy() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->excludeDir($dir1);
		$selection->excludeDir($dir2);

		$copy = $selection->copy();
		$this->assertEquals('FQ\Query\Selection\DirSelection', get_class($copy));
		$this->assertNotEquals($selection, $copy);
		$this->assertEquals(array(), $selection->getIncludedDirsById());
		$this->assertEquals(array(), $selection->getIncludedDirsByDir());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
		$this->assertEquals(array($dir1, $dir2), $selection->getExcludedDirsByDir());
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