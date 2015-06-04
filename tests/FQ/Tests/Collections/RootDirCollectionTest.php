<?php

namespace FQ\Tests\Collections;

use FQ\Collections\RootDirCollection;

class RootDirCollectionTest extends AbstractDirCollectionTests {

	/**
	 * @return RootDirCollection
	 */
	protected function dirCollection() {
		return parent::dirCollection();
	}

	/**
	 * @return RootDirCollection
	 */
	protected function _createNewDirCollection() {
		return new RootDirCollection();
	}

	/**
	 * @return \FQ\Dirs\RootDir
	 */
	protected function _createNewDir() {
		return $this->_newActualRootDir();
	}

	protected function _addDirToCollection($dir = null, $index = null) {
		$dir = $dir === null ? $this->_createNewDir() : $dir;
		$collection = $this->dirCollection();
		return $collection->addRootDir($dir, $index);
	}


	public function testCreateNewDirCollection() {
		$dirCollection = new RootDirCollection();
		$this->assertNotNull($dirCollection);
		$this->assertTrue($dirCollection instanceof RootDirCollection);
	}

	public function testAddDirIsDisabled() {
		$this->setExpectedException('FQ\Exceptions\DirCollectionException', 'Use addRootDir() to add directories');
		$collection = $this->dirCollection();
		$collection->addDir($this->_createNewDir());
	}

	public function testAddRootDir() {
		$this->_addDirToCollection();
		$this->assertEquals(1, $this->dirCollection()->totalDirs());
	}

	public function testGetDir() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDir($dir));
		$this->assertEquals($dir, $this->dirCollection()->getDir(self::ROOT_DIR_DEFAULT_ID));
		$this->assertEquals($dir, $this->dirCollection()->getDir(0));
	}
	public function testGetDirById() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDirById(self::ROOT_DIR_DEFAULT_ID));
	}
	public function testGetDirByIndex() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDirByIndex(0));
	}

	public function testGetBasePaths() {
		$this->_addDirToCollection();
		$this->assertEquals(array(self::ROOT_DIR_DEFAULT_BASE_PATH), $this->dirCollection()->getBasePaths());
	}
}