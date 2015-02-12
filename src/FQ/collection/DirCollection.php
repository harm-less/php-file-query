<?php

namespace FQ\Collection;

use FQ\Core\Exceptionable;
use FQ\Dirs\Dir;
use FQ\Exceptions\ExceptionableException;

class DirCollection extends Exceptionable {

	/**
	 * @var Dir[]
	 */
	private $_dirs = array();

	function __construct() {
		parent::__construct();


	}

	/**
	 * @param Dir $dir
	 * @param null|int $index
	 * @throws ExceptionableException
	 * @return Dir
	 */
	public function addDir(Dir $dir, $index = null) {
		$totalDirs = $this->totalDirs();
		if (is_int($index) && $totalDirs < $index) {
			return $this->_throwError(sprintf('Trying to add dir, but the provided index of "%s" is to high. There are currently %s dirs.', $index, $totalDirs));
		}
		array_splice($this->_dirs, $index === null ? $totalDirs - 1 : $index, 0, array($dir));
		return $dir;
	}

	public function getDir($dir) {
		if (is_string($dir)) {
			$dirObj = $this->getDirById($dir);
			if ($dirObj !== null) {
				return $dirObj;
			}
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
	 * @throws ExceptionableException
	 * @return Dir
	 */
	public function getDirByIndex($index) {
		if (!is_int($index)) {
			return $this->_throwError(sprintf('Index must be a number but is an %s', gettype($index)), true);
		}

		$totalDirs = $this->totalDirs();
		if ($totalDirs - 1 < $index) {
			return $this->_throwError(sprintf('Trying to get dir by a certain index, but the provided index of "%s" is to high. There are currently %s dirs.', $index, $totalDirs));
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