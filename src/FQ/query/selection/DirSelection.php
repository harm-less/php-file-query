<?php

namespace FQ\Query\Selection;

use FQ\Dirs\Dir;
use FQ\Exceptions\FileQueryException;

class DirSelection {

	private $_dirsIncluded;
	private $_dirsExcluded;

	private $_dirsIncludedId;
	private $_dirsExcludedId;

	const FILTER_INCLUDE = 'include';
	const FILTER_EXCLUDE = 'exclude';

	function __construct() {
		$this->reset();
	}

	/**
	 * Include a directory to the selection by its ID
	 * @param string $id ID of the directory
	 */
	public function includeDirById($id) {
		$this->_addSelectionDir(self::FILTER_INCLUDE);
		$this->_dirsIncludedId[] = $id;
	}
	public function getIncludedDirsById() {
		return $this->_dirsIncludedId;
	}

	public function hasIncludedDirsById() {
		$includedDirs = $this->getIncludedDirsById();
		return count($includedDirs) > 0;
	}

	/**
	 * Exclude a directory from the selection by its ID
	 * @param string $id ID of the directory
	 */
	public function excludeDirById($id) {
		$this->_addSelectionDir(self::FILTER_EXCLUDE);
		$this->_dirsExcludedId[] = $id;
	}
	public function getExcludedDirsById() {
		return $this->_dirsExcludedId;
	}

	public function hasExcludedDirsById() {
		$excludedDirs = $this->getExcludedDirsById();
		return count($excludedDirs) > 0;
	}

	/**
	 * Include a directory to the selected
	 * @param Dir $dir Directory instance
	 */
	public function includeDir(Dir $dir) {
		$this->_addSelectionDir(self::FILTER_INCLUDE);
		$this->_dirsIncluded[] = $dir;
	}
	/**
	 * @return Dir[]
	 */
	public function getIncludedDirsByDir() {
		return $this->_dirsIncluded;
	}

	public function hasIncludedDirsByDir() {
		$includedDirs = $this->getIncludedDirsByDir();
		return count($includedDirs) > 0;
	}
	/**
	 * Exclude a directory from the selection
	 * @param Dir $dir Directory instance
	 */
	public function excludeDir(Dir $dir) {
		$this->_addSelectionDir(self::FILTER_EXCLUDE);
		$this->_dirsExcluded[] = $dir;
	}
	/**
	 * @return Dir[]
	 */
	public function getExcludedDirsByDir() {
		return $this->_dirsExcluded;
	}

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
}