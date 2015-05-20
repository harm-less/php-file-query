<?php

namespace FQ\Tests\Collections;

use FQ\Collections\DirCollection;
use FQ\Dirs\Dir;

class DirCollectionTest extends AbstractDirCollectionTests {

	public function testCreateNewDirCollection() {
		$dirCollection = new DirCollection();
		$this->assertNotNull($dirCollection);
		$this->assertTrue($dirCollection instanceof DirCollection);
	}

	public function testAddDir() {
		$dir = $this->_addDirToCollection();
		$this->assertNotNull($dir);
		$this->assertEquals(1, $this->dirCollection()->totalDirs());
	}

	public function testAddDirAtIndexZero() {
		$dir = $this->_addDirToCollection(null, 0);
		$this->assertNotNull($dir);
		$this->assertEquals(1, $this->dirCollection()->totalDirs());
	}
	public function testAddDirAtIndexOne() {
		$this->setExpectedException('FQ\Exceptions\DirCollectionException', 'Trying to add dir, but the provided index of "1" is to high. There are currently 0 dirs.');
		$this->_addDirToCollection(null, 1);
	}
	public function testAddMultipleDirs() {
		$firstDir = $this->_addDirToCollection();
		$secondDir = $this->_addDirToCollection();
		$this->assertNotNull($firstDir);
		$this->assertNotNull($secondDir);
		$this->assertEquals(2, $this->dirCollection()->totalDirs());
	}

	public function testRemoveDir() {
		$firstDir = $this->_addDirToCollection();
		$secondDir = $this->_addDirToCollection();
		$collection = $this->dirCollection();
		$this->assertEquals($secondDir, $collection->removeDir($secondDir));
		$this->assertEquals(array($firstDir), $collection->dirs());
	}
	public function testRemoveDirNotPresentInTheCollection() {
		$collection = $this->dirCollection();
		$this->assertFalse($collection->removeDir($this->_newActualDir()));
	}
	public function testRemoveDirById() {
		$firstDir = $this->_addDirToCollection();
		$this->_addDirToCollection(new Dir('custom-id', 'dir'));
		$collection = $this->dirCollection();
		$this->assertTrue($collection->removeDirById('custom-id'));
		$this->assertEquals(array($firstDir), $collection->dirs());
	}
	public function testRemoveDirByIdNotPresentInTheCollection() {
		$collection = $this->dirCollection();
		$this->assertFalse($collection->removeDirById('does-not-exist'));
	}
	public function testRemoveDirAtIndex() {
		$firstDir = $this->_addDirToCollection();
		$this->_addDirToCollection();
		$collection = $this->dirCollection();
		$this->assertTrue($collection->removeDirAtIndex(1));
		$this->assertEquals(array($firstDir), $collection->dirs());
	}
	public function testRemoveDirAtIndexNotPresentInTheCollection() {
		$collection = $this->dirCollection();
		$this->assertFalse($collection->removeDirAtIndex(2));
	}
	public function testRemoveAllDirs() {
		$this->_addDirToCollection();
		$this->_addDirToCollection();
		$collection = $this->dirCollection();
		$collection->removeAllDirs();
		$this->assertEquals(array(), $collection->dirs());
	}

	public function testGetDirByCustomId() {
		$dir = $this->_addDirToCollection();
		$dir->id(self::CHILD_DIR_DEFAULT_ID);
		$this->assertEquals($dir, $this->dirCollection()->getDirById(self::CHILD_DIR_DEFAULT_ID));
	}
	public function testGetDirByIdThatDoesNotExist() {
		$this->assertNull($this->dirCollection()->getDirById('id_that_does_not_exist'));
	}

	public function testGetDirByIndex() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDirByIndex(0));
	}
	public function testGetDirByIndexWithTwoDirsAndTheSecondOneHasBeenAddedToIndexOne() {
		$firstDir = $this->_addDirToCollection();
		$secondDir = $this->_addDirToCollection(null, 0);
		$this->assertEquals($secondDir, $this->dirCollection()->getDirByIndex(0));
		$this->assertEquals($firstDir, $this->dirCollection()->getDirByIndex(1));
	}
	public function testGetDirByIndexIfTheIndexIsNotAnInteger() {
		$this->setExpectedException('FQ\Exceptions\DirCollectionException', 'Index must be a number but is a string');
		$this->dirCollection()->getDirByIndex('not_an_int');
	}
	public function testGetDirByIndexIfTheIndexIsTooHigh() {
		$this->setExpectedException('FQ\Exceptions\DirCollectionException', 'Trying to get dir by a certain index, but the provided index of "1" is to high. There are currently 0 dirs');
		$this->dirCollection()->getDirByIndex(1);
	}

	public function testGetDir() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDir($dir));
		$this->assertEquals($dir, $this->dirCollection()->getDir(self::CHILD_DIR_DEFAULT_ID));
		$this->assertEquals($dir, $this->dirCollection()->getDir(0));
		$this->assertNull($this->dirCollection()->getDir(null));
	}

	public function testGetPaths() {
		$this->_addDirToCollection();
		$this->assertEquals(array(self::DIR_ABSOLUTE_DEFAULT), $this->dirCollection()->getPaths());
	}

	public function testIsInCollection() {
		$dir = $this->_addDirToCollection();
		$this->assertTrue($this->dirCollection()->isInCollection($dir));
		$this->assertFalse($this->dirCollection()->isInCollection($this->_createNewDir()));
	}
}