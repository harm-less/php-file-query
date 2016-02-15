<?php

namespace FQ\Query\Selection;

use FQ\Dirs\Dir;
use FQ\Exceptions\FileQueryException;
use FQ\Files;

class DirSelection {

	private $_dirsIncluded;
	private $_dirsExcluded;

	private $_dirsIncludedId;
	private $_dirsExcludedId;

	private $_invalidated;

	private $_isLocked;

	const FILTER_INCLUDE = 'include';
	const FILTER_EXCLUDE = 'exclude';

	function __construct() {
		$this->reset();
	}

	/**
	 * Include a directory to the selection by its ID
	 * @param string $id ID of the directory
	 * @return bool
	 */
	public function includeDirById($id) {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_addSelectionDir(self::FILTER_INCLUDE);
		$this->_dirsIncludedId[] = $id;
		return true;
	}
	/**
	 * @param string $id
	 * @return bool|null
	 */
	public function removeIncludedDirById($id) {
		if ($this->isLocked()) {
			return null;
		}
		if (($index = array_search($id, $this->_dirsIncludedId)) !== false) {
			$this->invalidate();
			array_splice($this->_dirsIncludedId, $index, 1);
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function removeAllIncludedDirsById() {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_dirsIncludedId = array();
		return true;
	}
	/**
	 * @return string[]
	 */
	public function getIncludedDirsById() {
		return $this->_dirsIncludedId;
	}
	/**
	 * @return bool
	 */
	public function hasIncludedDirsById() {
		$includedDirs = $this->getIncludedDirsById();
		return count($includedDirs) > 0;
	}

	/**
	 * Exclude a directory from the selection by its ID
	 * @param string $id ID of the directory
	 * @return bool
	 */
	public function excludeDirById($id) {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_addSelectionDir(self::FILTER_EXCLUDE);
		$this->_dirsExcludedId[] = $id;
		return true;
	}
	/**
	 * @param string $id
	 * @return bool
	 */
	public function removeExcludedDirById($id) {
		if ($this->isLocked()) {
			return null;
		}
		if (($index = array_search($id, $this->_dirsExcludedId)) !== false) {
			$this->invalidate();
			array_splice($this->_dirsExcludedId, $index, 1);
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function removeAllExcludedDirsById() {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_dirsExcludedId = array();
		return true;
	}
	/**
	 * @return string[]
	 */
	public function getExcludedDirsById() {
		return $this->_dirsExcludedId;
	}
	/**
	 * @return bool
	 */
	public function hasExcludedDirsById() {
		$excludedDirs = $this->getExcludedDirsById();
		return count($excludedDirs) > 0;
	}

	/**
	 * Include a directory to the selected
	 * @param Dir $dir Directory instance
	 * @return bool
	 */
	public function includeDir(Dir $dir) {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_addSelectionDir(self::FILTER_INCLUDE);
		$this->_dirsIncluded[] = $dir;
		return true;
	}
	/**
	 * @param Dir $dir
	 * @return bool|null
	 */
	public function removeIncludedDir(Dir $dir) {
		if ($this->isLocked()) {
			return null;
		}
		if (($index = array_search($dir, $this->_dirsIncluded)) !== false) {
			$this->invalidate();
			array_splice($this->_dirsIncluded, $index, 1);
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function removeAllIncludedDirs() {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_dirsIncluded = array();
		return true;
	}
	/**
	 * @return Dir[]
	 */
	public function getIncludedDirsByDir() {
		return $this->_dirsIncluded;
	}
	/**
	 * @return bool
	 */
	public function hasIncludedDirsByDir() {
		$includedDirs = $this->getIncludedDirsByDir();
		return count($includedDirs) > 0;
	}

	/**
	 * Exclude a directory from the selection
	 * @param Dir $dir Directory instance
	 * @return bool
	 */
	public function excludeDir(Dir $dir) {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_addSelectionDir(self::FILTER_EXCLUDE);
		$this->_dirsExcluded[] = $dir;
		return true;
	}
	/**
	 * @param Dir $dir
	 * @return bool
	 */
	public function removeExcludedDir(Dir $dir) {
		if ($this->isLocked()) {
			return null;
		}
		if (($index = array_search($dir, $this->_dirsExcluded)) !== false) {
			$this->invalidate();
			array_splice($this->_dirsExcluded, $index, 1);
			return true;
		}
		return false;
	}
	/**
	 * @return bool
	 */
	public function removeAllExcludedDirs() {
		if ($this->isLocked()) {
			return false;
		}
		$this->invalidate();
		$this->_dirsExcluded = array();
		return true;
	}
	/**
	 * @return Dir[]
	 */
	public function getExcludedDirsByDir() {
		return $this->_dirsExcluded;
	}
	/**
	 * @return bool
	 */
	public function hasExcludedDirsByDir() {
		$excludedDirs = $this->getExcludedDirsByDir();
		return count($excludedDirs) > 0;
	}

	public function hasIncludedDirs() {
		return $this->hasIncludedDirsByDir() || $this->hasIncludedDirsById();
	}
	public function hasExcludedDirs() {
		return $this->hasExcludedDirsByDir() || $this->hasExcludedDirsById();
	}

	protected function _addSelectionDir($filterType) {
		switch ($filterType) {
			case self::FILTER_INCLUDE :
				if ($this->hasExcludedDirs()) {
					throw new FileQueryException('Cannot include a dir when you\'ve already defined excluded directories');
				}
				break;
			default :
				if ($this->hasIncludedDirs()) {
					throw new FileQueryException('Cannot exclude a dir when you\'ve already defined included directories');
				}
				break;
		}
	}

	/**
	 * @param Dir[] $availableDirs
	 */
	public function validateQuerySelection($availableDirs) {

		$isSelection = $this->hasIncludedDirs() || $this->hasExcludedDirs();
		$availableIds = array();
		if ($isSelection) {
			// collect all available ids beforehand, this way we can check against a string array instead of multiple objects
			foreach ($availableDirs as $availableDir) {
				$availableIds[] = $availableDir->id();
			}
		}

		if ($isSelection && count($availableIds)) {
			$neededIds = array();

			// collect all selection dirs by id
			if ($this->hasIncludedDirsById()) {
				$neededIds = array_merge($neededIds, $this->getIncludedDirsById());
			}
			else if ($this->hasExcludedDirsById()) {
				$neededIds = array_merge($neededIds, $this->getExcludedDirsById());
			}

			// collect all selection dirs by dir objects
			$dirIds = array();
			if ($this->hasIncludedDirsByDir()) {
				foreach ($this->getIncludedDirsByDir() as $includedDir) {
					$dirIds[] = $includedDir->id();
				}
			}
			else if ($this->hasExcludedDirsByDir()) {
				foreach ($this->getExcludedDirsByDir() as $excludedDir) {
					$dirIds[] = $excludedDir->id();
				}
			}

			$neededIds = array_merge($neededIds, $dirIds);

			if (count(array_intersect($neededIds, $availableIds)) !== count($neededIds)) {
				throw new FileQueryException(sprintf('Query selection validation failed because one ore more selection IDs (%s) could not be found in the available directories. Available directory ids are "%s"', implode(', ', array_diff($neededIds, $availableIds)), implode(', ', $availableIds)));
			}
		}
	}

	/**
	 * @param Dir[] $availableDirs
	 * @return Dir[]
	 */
	public function getSelection($availableDirs) {

		$this->validateQuerySelection($availableDirs);

		$this->_invalidated = false;

		if ($this->hasIncludedDirs() || $this->hasExcludedDirs()) {

			$availableDirsTemp = array();
			foreach ($availableDirs as $availableDir) {
				$availableDirsTemp[$availableDir->id()] = $availableDir;
			}

			if ($this->hasIncludedDirs()) {
				$selection = array();
				if ($this->hasIncludedDirsById()) {
					foreach ($this->getIncludedDirsById() as $includedDirId) {
						$selection[] = $availableDirsTemp[$includedDirId];
					}
				}
				if ($this->hasIncludedDirsByDir()) {
					foreach ($this->getIncludedDirsByDir() as $includedDir) {
						$selection[] = $availableDirsTemp[$includedDir->id()];
					}
				}
			}
			else {
				$selection = $availableDirsTemp;
				if ($this->hasExcludedDirsById()) {
					foreach ($this->getExcludedDirsById() as $includedDirId) {
						unset($selection[$includedDirId]);
					}
				}
				if ($this->hasExcludedDirsByDir()) {
					foreach ($this->getExcludedDirsByDir() as $includedDir) {
						unset($selection[$includedDir->id()]);
					}
				}
				// reset keys
				$selection = array_values($selection);
			}

			return $selection;
		}
		else {
			return $availableDirs;
		}
	}

	public function reset() {
		$this->_dirsIncluded = array();
		$this->_dirsExcluded = array();

		$this->_dirsIncludedId = array();
		$this->_dirsExcludedId = array();
	}

	public function unsafeImport($includedIds, $includedDirs, $excludedIds, $excludedDirs) {
		$this->_dirsIncluded = $includedDirs === null ? array() : $includedDirs;
		$this->_dirsExcluded = $excludedDirs === null ? array() : $excludedDirs;

		$this->_dirsIncludedId = $includedIds === null ? array() : $includedIds;
		$this->_dirsExcludedId = $excludedIds === null ? array() : $excludedIds;
	}

	public function invalidate() {
		$this->_invalidated = true;
	}
	public function isInvalidated() {
		return $this->_invalidated;
	}

	public function lock() {
		$this->_isLocked = true;
	}
	public function unlock() {
		$this->_isLocked = false;
	}
	public function isLocked() {
		return $this->_isLocked === true;
	}

	public function copy() {
		$clone = $this->_createInstance();
		$clone->unsafeImport($this->_dirsIncludedId, $this->_dirsIncluded, $this->_dirsExcludedId, $this->_dirsExcluded);
		return $clone;
	}
	protected function _createInstance() {
		return new DirSelection();
	}
}