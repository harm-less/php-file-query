<?php

namespace FQ\Collections;

use FQ\Dirs\ChildDir;
use FQ\Dirs\Dir;
use FQ\Exceptions\DirCollectionException;
use FQ\Exceptions\FilesException;

class ChildDirCollection extends DirCollection {

	/**
	 * Wrapper function for adding child dirs
	 *
	 * @param ChildDir $dir
	 * @param null $index
	 * @return Dir
	 */
	public function addChildDir(ChildDir $dir, $index = null) {
		return parent::addDir($dir, $index);
	}

	/**
	 * This method is disabled to ensure only ChildDir instances are being added to the collection
	 *
	 * @param Dir $dir
	 * @param null $index
	 * @return FilesException
	 * @throws FilesException
	 */
	public function addDir(Dir $dir, $index = null) {
		throw new FilesException('Use addChildDir() to add directories');
	}


	/**
	 * @param mixed $dir
	 * @return ChildDir|null
	 */
	public function getDir($dir) {
		return parent::getDir($dir);
	}

	/**
	 * @param string $id
	 * @return null|ChildDir
	 */
	public function getDirById($id) {
		return parent::getDirById($id);
	}

	/**
	 * @param int $index
	 * @throws DirCollectionException
	 * @return ChildDir
	 */
	public function getDirByIndex($index) {
		return parent::getDirByIndex($index);
	}

	/**
	 * @return ChildDir[]
	 */
	public function dirs() {
		return parent::dirs();
	}
}