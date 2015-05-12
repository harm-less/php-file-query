<?php

namespace FQ\Tests\Collections;

use FQ\Collections\ChildDirCollection;

class ChildDirCollectionTest extends AbstractDirCollectionTests {

	/**
	 * @return ChildDirCollection
	 */
	protected function dirCollection() {
		return parent::dirCollection();
	}

	/**
	 * @return ChildDirCollection
	 */
	protected function _createNewDirCollection() {
		return new ChildDirCollection();
	}

	/**
	 * @return \FQ\Dirs\ChildDir
	 */
	protected function _createNewDir() {
		return $this->_newActualChildDir();
	}

	protected function _addDirToCollection($dir = null, $index = null) {
		$dir = $dir === null ? $this->_createNewDir() : $dir;
		$collection = $this->dirCollection();
		return $collection->addChildDir($dir, $index);
	}


	public function testCreateNewDirCollection() {
		$dirCollection = new ChildDirCollection();
		$this->assertNotNull($dirCollection);
		$this->assertTrue($dirCollection instanceof ChildDirCollection);
	}

	public function testAddDirIsDisabled() {
		$this->setExpectedException('FQ\Exceptions\DirCollectionException', 'Use addChildDir() to add directories');
		$collection = $this->dirCollection();
		$collection->addDir($this->_createNewDir());
	}

	public function testAddChildDir() {
		$this->_addDirToCollection();
		$this->assertEquals(1, $this->dirCollection()->totalDirs());
	}

	public function testGetDir() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDir($dir));
		$this->assertEquals($dir, $this->dirCollection()->getDir(self::CHILD_DIR_DEFAULT));
		$this->assertEquals($dir, $this->dirCollection()->getDir(0));
	}
	public function testGetDirById() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDirById(self::CHILD_DIR_DEFAULT));
	}
	public function testGetDirByIndex() {
		$dir = $this->_addDirToCollection();
		$this->assertEquals($dir, $this->dirCollection()->getDirByIndex(0));
	}
}