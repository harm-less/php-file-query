<?php

namespace FQ\Collections;

use FQ\Dirs\Dir;
use FQ\Exceptions\DirCollectionException;

class DirCollection {

	/**
	 * @var Dir[]
	 */
	private $_dirs = array();

	/**
	 * @param Dir $dir
	 * @param null|int $index
	 * @throws DirCollectionException
	 * @return Dir|false
	 */
	public function addDir(Dir $dir, $index = null) {
		$totalDirs = $this->totalDirs();
		if (is_int($index) && $totalDirs < $index) {
			throw new DirCollectionException(sprintf('Trying to add dir, but the provided index of "%s" is to high. There are currently %s dirs.', $index, $totalDirs));
		}
		array_splice($this->_dirs, $index === null ? $totalDirs - 1 : $index, 0, array($dir));
		return $dir;
	}

	/**
	 * @param mixed $dir
	 * @return Dir|null
	 */
	public function getDir($dir) {
		if (is_string($dir)) {
			return $this->getDirById($dir);
		}
		else if (is_int($dir)) {
			return $this->getDirByIndex($dir);
		}
		else if (is_object($dir) && $this->isInCollection($dir)) {
			return $dir;
		}
		return null;
	}

	/**
	 * @param string $id
	 * @return null|Dir
	 */
	public function getDirById($id) {
		foreach ($this->dirs() as $dir) {
			if ($dir->id() == $id) return $dir;
		}
		return null;
	}

	/**
	 * @param int $index
	 * @throws DirCollectionException
	 * @return Dir
	 */
	public function getDirByIndex($index) {
		if (!is_int($index)) {
			throw new DirCollectionException(sprintf('Index must be a number but is a %s', gettype($index)));
		}

		$totalDirs = $this->totalDirs();
		if ($totalDirs - 1 < $index) {
			throw new DirCollectionException(sprintf('Trying to get dir by a certain index, but the provided index of "%s" is to high. There are currently %s dirs.', $index, $totalDirs));
		}
		$dirs = $this->dirs();
		return $dirs[$index];
	}

	/**
	 * @return string[]
	 */
	public function getPaths() {
		$paths = array();
		foreach ($this->dirs() as $dir) {
			$paths[] = $dir->dir();
		};
		return $paths;
	}

	/**
	 * @param Dir $dir Dir that will be checked
	 * @return bool Returns true if dir is in the collection and false when it's not
	 */
	public function isInCollection(Dir $dir) {
		foreach ($this->dirs() as $dirTemp) {
			if ($dir === $dirTemp) return true;
		}
		return false;
	}

	/**
	 * @return Dir[]
	 */
	public function dirs() {
		return $this->_dirs;
	}

	/**
	 * @return int Total amount of directories in this collection
	 */
	public function totalDirs() {
		return count($this->dirs());
	}
}
?>