<?php

namespace FQ\Collections\Dirs;

use FQ\Dirs\Dir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\ExceptionableException;
use FQ\Exceptions\FileException;

class RootDirCollection extends DirCollection {

	function __construct() {
		parent::__construct();
	}

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
	 * @return FileException
	 * @throws FileException
	 */
	public function addDir(Dir $dir, $index = null) {
		throw new FileException('Use addRootDir() to add directories');
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
	 * @throws ExceptionableException
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