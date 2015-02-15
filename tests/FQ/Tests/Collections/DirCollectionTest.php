<?php

namespace FQ\Tests\Collections;

use FQ\Collections\Dirs\DirCollection;
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
		$dir = $this->_addDirToCollection(null, 1);
		$this->assertFalse($dir);
		$this->assertEquals(0, $this->dirCollection()->totalDirs());
	}
	public function testAddDirAtIndexOneWithThrowErrorsAtTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');
		$collection = $this->dirCollection();
		$collection->throwErrors(true);
		$this->_addDirToCollection(null, 1);
	}
	public function testAddMultipleDirs() {
		$firstDir = $this->_addDirToCollection();
		$secondDir = $this->_addDirToCollection();
		$this->assertNotNull($firstDir);
		$this->assertNotNull($secondDir);
		$this->assertEquals(2, $this->dirCollection()->totalDirs());
	}

	public function testGetDirByDefaultId() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDirById(self::DIR_ABSOLUTE_DEFAULT));
	}
	public function testGetDirByCustomId() {
		$dir = new Dir(self::DIR_ABSOLUTE_DEFAULT);
		$dir->id(self::DIR_CUSTOM_ID);
		$this->_addDirToCollection($dir);
		$this->assertEquals($dir, $this->dirCollection()->getDirById(self::DIR_CUSTOM_ID));
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
		$this->assertFalse($this->dirCollection()->getDirByIndex('not_an_int'));
	}
	public function testGetDirByIndexIfTheIndexIsNotAnIntegerAndThrowErrorsIsSetToTrue() {
		$collection = $this->dirCollection();
		$collection->throwErrors(true);
		$this->assertFalse($this->dirCollection()->getDirByIndex('not_an_int'));
	}
	public function testGetDirByIndexIfTheIndexIsTooHighAndThrowErrorsIsSetToTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');
		$collection = $this->dirCollection();
		$collection->throwErrors(true);
		$this->dirCollection()->getDirByIndex(1);
	}

	public function testGetDir() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDir($dir));
		$this->assertEquals($dir, $this->dirCollection()->getDir(self::DIR_ABSOLUTE_DEFAULT));
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