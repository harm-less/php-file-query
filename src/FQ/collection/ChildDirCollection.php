<?php

namespace FQ\Collection;

use FQ\Dirs\ChildDir;
use FQ\Dirs\Dir;
use FQ\Exceptions\FileException;

class ChildDirCollection extends DirCollection {

	function __construct() {
		parent::__construct();
	}

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
	 * @return FileException
	 * @throws FileException
	 */
	public function addDir(Dir $dir, $index = null) {
		throw new FileException('Use addChildDir() to add directories');
	}
}