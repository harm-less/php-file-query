<?php

namespace FQ\Collections;

use FQ\Dirs\Dir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\DirCollectionException;

class RootDirCollection extends DirCollection {

	/**
	 * Wrapper function for adding child dirs
	 *
	 * @param RootDir $dir
	 * @param null $index
	 * @return Dir
	 */
	public function addRootDir(RootDir $dir, $index = null) {
		return parent::addDir($dir, $index);
	}

	/**
	 * This method is disabled to ensure only RootDir instances are being added to the collection
	 *
	 * @param Dir $dir
	 * @param null $index
	 * @return DirCollectionException
	 * @throws DirCollectionException
	 */
	public function addDir(Dir $dir, $index = null) {
		throw new DirCollectionException('Use addRootDir() to add directories');
	}

	/**
	 * @param mixed $dir
	 * @return RootDir|null
	 */
	public function getDir($dir) {
		return parent::getDir($dir);
	}

	/**
	 * @param string $id
	 * @return null|RootDir
	 */
	public function getDirById($id) {
		return parent::getDirById($id);
	}

	/**
	 * @param int $index
	 * @throws DirCollectionException
	 * @return RootDir
	 */
	public function getDirByIndex($index) {
		return parent::getDirByIndex($index);
	}

	/**
	 * @return RootDir[]
	 */
	public function dirs() {
		return parent::dirs();
	}
}