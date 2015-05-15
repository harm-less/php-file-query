<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\FileQueryException;
use FQ\Files;
use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\RootSelection;

class FilesQuery {

	/**
	 * @var Files
	 */
	private $_files;

	/**
	 * @var RootSelection
	 */
	private $_rootDirSelection;

	/**
	 * @var ChildSelection
	 */
	private $_childDirSelection;

	/**
	 * @var FilesQueryChild[]
	 */
	private $_cachedQueryChildren;

	/**
	 * @var FilesQueryChild[]
	 */
	private $_currentQueryChildren;

	/**
	 * @var RootDir[]
	 */
	private $_cachedQueryRootDirs;

	/**
	 * @var bool Indicator if the query has run or not
	 */
	private $_hasRun;

	/**
	 * @var string Active file name in the latest query
	 */
	private $_queriedFileName;

	/**
	 * @var bool Reverse the query
	 */
	private $_reverse;

	/**
	 * @var FilesQueryRequirements
	 */
	private $_requirements;

	/**
	 * @var string[] Filters of the query
	 */
	private $_filters;

	/**
	 * Constants determining preliminary filters types
	 */
	const FILTER_NONE = 'filter_none';
	const FILTER_EXISTING = 'filter_existing';

	/**
	 * @param Files $files
	 */
	function __construct(Files $files) {
		$this->_requirements = new FilesQueryRequirements();
		$this->_files = $files;

		$this->reset();
	}

	public function reset() {
		$this->_cachedQueryRootDirs = null;
		$this->_currentQueryChildren = array();

		$this->requirements()->removeAll();
		$this->filters(self::FILTER_EXISTING, false);

		$this->_queriedFileName = null;
		$this->_reverse = false;
		$this->_hasRun = false;
	}
	public function resetSelection() {
		$this->getRootDirSelection()->reset();
		$this->getChildDirSelection()->reset();
	}

	/**
	 * @return FilesQueryRequirements
	 */
	public function requirements() {
		return $this->_requirements;
	}

	/**
	 * @param bool $reverse Reverse query results
	 */
	public function reverse($reverse) {
		$this->_reverse = $reverse;
	}

	/**
	 * @param string|string[] $filters
	 * @param bool $mergeWithExistingFilters If the new filters should merge with the old ones. This even works when no
	 * filters were present to begin with
	 * @return string[]
	 */
	public function filters($filters = null, $mergeWithExistingFilters = true) {
		if ($filters !== null) {
			$newFilters = is_string($filters) ? (array) $filters : $filters;
			if ($mergeWithExistingFilters) {
				$this->_filters = array_merge(is_array($this->_filters) ? $this->_filters : array(), $newFilters);
			}
			else {
				$this->_filters = $newFilters;
			}
		}
		return $this->_filters;
	}

	public function setRootDirSelection(RootSelection $selection = null) {
		$this->_rootDirSelection = $selection;
	}
	public function getRootDirSelection($createNewSelection = true) {
		if ($this->_rootDirSelection === null && $createNewSelection) {
			$this->_rootDirSelection = new RootSelection();
		}
		return $this->_rootDirSelection;
	}

	public function setChildDirSelection(ChildSelection $selection = null) {
		$this->_childDirSelection = $selection;
	}
	public function getChildDirSelection($createNewSelection = true) {
		if ($this->_childDirSelection === null && $createNewSelection) {
			$this->_childDirSelection = new ChildSelection();
		}
		return $this->_childDirSelection;
	}

	/**
	 * @param ChildDir $childDir
	 * @return bool If a ChildDir exists in the Files instance it will return true, otherwise it will return false
	 */
	public function isValidChildDir(ChildDir $childDir) {
		return $this->files()->isChildDirOf($childDir);
	}

	public function queriedFileName() {
		return $this->_queriedFileName;
	}

	public function queryChildDirs() {
		return $this->_currentQueryChildren;
	}

	public function files() {
		return $this->_files;
	}

	public function hasRun() {
		return $this->_hasRun;
	}
	public function isReversed() {
		return $this->_reverse;
	}

	protected function _hasRun() {
		if (!$this->hasRun()) {
			throw new FileQueryException('You must first call the "run" method before you can retrieve query information');
		}
		return true;
	}

	/**
	 * @param string $fileName The name of the file the query will be executing
	 * @return null|string[]
	 */
	public function run($fileName) {
		$this->_queriedFileName = $fileName;
		$rootDirsSelection = $this->_getCachedRootDirSelection();

		$this->_currentQueryChildren = array();
		foreach ($this->getCurrentChildDirSelection() as $childDir) {
			$this->_currentQueryChildren[$childDir->id()] = $this->processQueryChild($childDir, $rootDirsSelection);
		}
		$this->_hasRun = true;
		return $this->listPaths();
	}

	/**
	 * @return RootDir[]
	 */
	public function getCurrentRootDirSelection() {
		return $this->getRootDirSelection()->getSelection($this->files()->rootDirs());
	}

	/**
	 * @return ChildDir[]
	 */
	public function getCurrentChildDirSelection() {
		return $this->getChildDirSelection()->getSelection($this->files()->childDirs());
	}

	protected function _getCachedRootDirSelection() {
		if ($this->_cachedQueryRootDirs === null) {
			$this->_cachedQueryRootDirs = $this->getCurrentRootDirSelection();
		}
		return $this->_cachedQueryRootDirs;
	}

	protected function processQueryChild(ChildDir $childDir, $rootSelection) {
		$queryChild = $this->_getQueryChild($childDir);
		$queryChild->reset();
		$queryChild->setRootDirs($rootSelection);
		if (!$this->requirements()->meetsRequirements($queryChild)) {
			return false;
		}
		return $queryChild;
	}

	/**
	 * @param ChildDir $childDir
	 * @return FilesQueryChild
	 */
	protected function _getQueryChild(ChildDir $childDir) {
		if (isset($this->_cachedQueryChildren[$childDir->id()])) {
			return $this->_cachedQueryChildren[$childDir->id()];
		}
		else {
			return new FilesQueryChild($this, $childDir);
		}
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return null|string[]
	 */
	public function listPaths() {
		if (!$this->_hasRun()) {
			return false;
		}

		$paths = array();
		foreach($this->queryChildDirs() as $childQuery) {
			// in case one of the child queries failed and returned false, do not try to add this to the list
			if ($childQuery !== false) {
				$paths = array_merge_recursive($paths, $childQuery->filteredAbsolutePaths());
			}
		}
		return $paths;
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return string[]
	 */
	public function listBasePaths() {
		if (!$this->_hasRun()) {
			return false;
		}

		$paths = array();
		foreach($this->queryChildDirs() as $childQuery) {
			$paths = array_merge($paths, $childQuery->filteredBasePaths());
		}
		return $paths;
	}

	/**
	 * @return bool
	 */
	public function hasPaths() {
		$paths = $this->listPaths();
		return !empty($paths);
	}
} 