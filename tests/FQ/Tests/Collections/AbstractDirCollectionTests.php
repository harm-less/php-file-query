<?php

namespace FQ\Tests\Collections;

use FQ\Collections\DirCollection;
use FQ\Dirs\Dir;
use FQ\Tests\AbstractFQTest;

class AbstractDirCollectionTests extends AbstractFQTest {

	protected $_dirCollection;

	protected function setUp()
	{
		parent::setUp();
		// Create a new FQ app,
		// since we need one pretty much everywhere
		$this->_dirCollection = $this->_createNewDirCollection();
	}

	/**
	 * @return DirCollection
	 */
	protected function dirCollection() {
		return $this->_dirCollection;
	}

	/**
	 * @return DirCollection
	 */
	protected function _createNewDirCollection() {
		return new DirCollection();
	}

	protected function _addDirToCollection(Dir $dir = null, $index = null) {
		$dir = $dir === null ? $this->_createNewDir() : $dir;
		$collection = $this->dirCollection();
		return $collection->addDir($dir, $index);
	}

	/**
	 * @return \FQ\Dirs\Dir
	 */
	protected function _createNewDir() {
		return $this->_newActualDir();
	}
}