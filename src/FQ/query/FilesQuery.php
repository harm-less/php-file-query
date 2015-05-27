<?php

namespace FQ\Query;

use FQ\Dirs\RootDir;
use FQ\Dirs\ChildDir;
use FQ\Exceptions\FileQueryException;
use FQ\Files;
use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\RootSelection;
use FQ\Utils\Dirs;

/**
 * Class FilesQuery
 * @package FQ\Query
 *
 * @todo Ability to lock selections if you have a reoccurring queries
 */
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
	private $_cachedRootDirs;
	/**
	 * @var RootDir[]
	 */
	private $_cachedQueryRootDirs;

	/**
	 * @var ChildDir[]
	 */
	private $_cachedChildDirs;
	/**
	 * @var ChildDir[]
	 */
	private $_cachedQueryChildDirs;

	/**
	 * @var array
	 */
	private $_cachedPaths;
	/**
	 * @var string[]
	 */
	private $_cachedPathsSimple;

	/**
	 * @var array
	 */
	private $_cachedRawPaths;
	/**
	 * @var string[]
	 */
	private $_cachedRawPathsSimple;

	/**
	 * @var bool Indicator if the query has run or not
	 */
	private $_hasRun;

	/**
	 * @var \Exception
	 */
	private $_queryError;

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
	const FILTER_EXISTING = 'filter_existing';

	/**
	 * @param Files $files
	 */
	function __construct(Files $files) {
		$this->_files = $files;

		$this->reset();
	}

	public function reset() {
		$this->_clearRootDirCache();
		$this->_clearChildDirCache();
		$this->_clearPathCache();

		$this->requirements()->removeAll();
		$this->filters(self::FILTER_EXISTING, false);

		$this->_queriedFileName = null;
		$this->_reverse = false;
		$this->_hasRun = false;
		$this->_queryError = null;
	}
	protected function _clearRootDirCache() {
		$this->_cachedQueryRootDirs = null;
	}
	protected function _clearChildDirCache() {
		$this->_cachedQueryChildDirs = null;
	}
	protected function _clearPathCache() {
		$this->_currentQueryChildren = array();

		$this->_cachedPaths = null;
		$this->_cachedPathsSimple = null;
		$this->_cachedRawPaths = null;
		$this->_cachedRawPathsSimple = null;
	}
	public function resetSelection() {
		$this->getRootDirSelection()->reset();
		$this->getChildDirSelection()->reset();
	}

	/**
	 * @return FilesQueryRequirements
	 */
	public function requirements() {
		if ($this->_requirements === null) {
			$this->_requirements = new FilesQueryRequirements($this);
		}
		return $this->_requirements;
	}

	/**
	 * @param bool $reverse Reverse query results
	 * @return bool
	 */
	public function reverse($reverse = null) {
		if (is_bool($reverse)) {
			$this->_reverse = $reverse;
		}
		return $this->_reverse;
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
			$this->_filters = array_unique($this->_filters);
		}
		return $this->_filters;
	}
	public function queryHasFilter($filter) {
		return in_array($filter, $this->filters());
	}

	/**
	 * @param RootSelection $rootDirSelection
	 */
	public function setRootDirSelection(RootSelection $rootDirSelection) {
		$this->_cachedQueryRootDirs = null;
		$this->_rootDirSelection = $rootDirSelection;
	}
	public function getRootDirSelection() {
		if ($this->_rootDirSelection === null) {
			$this->_rootDirSelection = new RootSelection();
		}
		return $this->_rootDirSelection;
	}

	/**
	 * @param ChildSelection $childDirSelection
	 */
	public function setChildDirSelection(ChildSelection $childDirSelection) {
		$this->_cachedQueryChildren = null;
		$this->_childDirSelection = $childDirSelection;
	}
	public function getChildDirSelection() {
		if ($this->_childDirSelection === null) {
			$this->_childDirSelection = new ChildSelection();
		}
		return $this->_childDirSelection;
	}

	/**
	 * @param ChildDir $childDir
	 * @return bool If a ChildDir exists in the Files instance it will return true, otherwise it will return false
	 */
	public function isValidChildDir(ChildDir $childDir) {
		return $this->files()->containsChildDir($childDir);
	}

	public function queriedFileName() {
		return $this->_queriedFileName;
	}

	/**
	 * @param bool $reversed
	 * @return FilesQueryChild[]
	 */
	public function queryChildDirs($reversed = false) {
		if ($reversed === true) {
			return array_reverse($this->_currentQueryChildren);
		}
		return $this->_currentQueryChildren;
	}

	public function files() {
		return $this->_files;
	}

	public function hasRun() {
		return $this->_hasRun;
	}
	public function queryError() {
		return $this->_queryError;
	}
	public function hasQueryError() {
		return $this->queryError() !== null;
	}
	public function isReversed() {
		return $this->_reverse;
	}

	protected function _hasRunCheck() {
		if (!$this->hasRun()) {
			throw new FileQueryException('You must first call the "run" method before you can retrieve query information');
		}
		return true;
	}

	/**
	 * @param string $fileName The name of the file the query will be executing
	 * @param bool $throwExceptions
	 * @throws \Exception
	 * @return bool
	 */
	public function run($fileName, $throwExceptions = false) {
		$files = $this->files();
		if ($files->totalRootDirs() === 0) {
			throw new FileQueryException(sprintf('Query is trying to run with file "%s" but no root directories are configured. Make sure sure you have added at least one root directory with Files::addRootDir() before you run a query', $fileName));
		}

		$this->_clearPathCache();

		$currentRootDirs = $files->rootDirs();
		if (!Dirs::equalDirs($this->_cachedRootDirs, $currentRootDirs) || $this->getRootDirSelection()->isInvalidated()) {
			$this->_clearRootDirCache();
		}
		$this->_cachedRootDirs = $currentRootDirs;
		$currentChildDirs = $files->childDirs();
		if (!Dirs::equalDirs($this->_cachedChildDirs, $currentChildDirs) || $this->getChildDirSelection()->isInvalidated()) {
			$this->_clearChildDirCache();
		}
		$this->_cachedChildDirs = $currentChildDirs;

		$this->_queriedFileName = $fileName;
		$rootDirsSelection = $this->_getCachedRootDirSelection();

		$this->_currentQueryChildren = array();
		foreach ($this->_getCachedChildDirSelection() as $childDir) {
			$this->_currentQueryChildren[$childDir->id()] = $this->_prepareQueryChild($childDir, $rootDirsSelection);
		}
		$this->_hasRun = true;
		$meetsRequirements = $this->requirements()->meetsRequirements(false);
		if ($meetsRequirements !== true) {
			$this->_queryError = $meetsRequirements;
			if ($throwExceptions === true) {
				throw new $this->_queryError;
			}
		}
		return $this->_queryError === null;
	}

	protected function _prepareQueryChild(ChildDir $childDir, $rootDirs) {
		$queryChild = $this->_getQueryChild($childDir);
		$queryChild->reset();
		$queryChild->setRootDirs($rootDirs);

		return $queryChild;
	}

	public function load() {
		$this->_hasRunCheck();

		if ($this->queryHasFilter(FilesQuery::FILTER_EXISTING)) {
			foreach ($this->listPathsSimple() as $path) {
				/** @noinspection PhpIncludeInspection */
				require_once $path;
			}
			return true;
		}
		else {
			throw new FileQueryException('Loading files requires the filter "existing"');
		}
	}

	/**
	 * @return RootDir[]
	 */
	public function getCurrentRootDirSelection() {
		return $this->getRootDirSelection()->getSelection($this->files()->rootDirs());
	}
	protected function _getCachedRootDirSelection() {
		if ($this->_cachedQueryRootDirs === null) {
			$this->_cachedQueryRootDirs = $this->getCurrentRootDirSelection();
		}
		return $this->_cachedQueryRootDirs;
	}

	/**
	 * @return ChildDir[]
	 */
	public function getCurrentChildDirSelection() {
		return $this->getChildDirSelection()->getSelection($this->files()->childDirs());
	}
	protected function _getCachedChildDirSelection() {
		if ($this->_cachedQueryChildDirs === null) {
			$this->_cachedQueryChildDirs = $this->getCurrentChildDirSelection();
		}
		return $this->_cachedQueryChildDirs;
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
			$queryChild = new FilesQueryChild($this, $childDir);
			$this->_cachedQueryChildren[$childDir->id()] = $queryChild;
			return $queryChild;
		}
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return null|string[]
	 */
	public function listPaths() {
		$this->_hasRunCheck();

		if ($this->_cachedPaths === null) {

			$paths = array();
			foreach ($this->queryChildDirs() as $childQuery) {
				// in case one of the child queries failed and returned false, do not try to add this to the list
				if ($childQuery !== false) {
					$paths = array_merge_recursive($paths, $childQuery->filteredAbsolutePaths());
				}
			}
			if ($this->isReversed()) {
				$paths = $this->reversePaths($paths);
			}
			$this->_cachedPaths = $paths;
		}
		return $this->_cachedPaths;
	}
	/**
	 * Returns a list of all the paths it found
	 *
	 * @return null|string[]
	 */
	public function listPathsSimple() {
		$this->_hasRunCheck();

		if ($this->_cachedPathsSimple === null) {
			$temp = array();
			foreach ($this->listPaths() as $rootDirPaths) {
				$temp = array_merge($temp, (array) $rootDirPaths);
			}
			$this->_cachedPathsSimple = $temp;
		}
		return $this->_cachedPathsSimple;
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return null|string[]
	 */
	public function listRawPaths() {
		$this->_hasRunCheck();

		if ($this->_cachedRawPaths === null) {

			$paths = array();
			foreach ($this->queryChildDirs() as $childQuery) {
				// in case one of the child queries failed and returned false, do not try to add this to the list
				if ($childQuery !== false) {
					$paths = array_merge_recursive($paths, $childQuery->rawAbsolutePaths());
				}
			}
			if ($this->isReversed()) {
				$paths = $this->reversePaths($paths);
			}
			$this->_cachedRawPaths = $paths;
		}
		return $this->_cachedRawPaths;
	}
	/**
	 * Returns a list of all the paths it found
	 *
	 * @return null|string[]
	 */
	public function listRawPathsSimple() {
		$this->_hasRunCheck();

		if ($this->_cachedRawPathsSimple === null) {
			$temp = array();
			foreach ($this->listRawPaths() as $rootDirPaths) {
				$temp = array_merge($temp, (array) $rootDirPaths);
			}
			$this->_cachedRawPathsSimple = $temp;
		}
		return $this->_cachedRawPathsSimple;
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return string[]
	 */
	public function listBasePaths() {
		$this->_hasRunCheck();

		$paths = array();
		foreach($this->queryChildDirs() as $childQuery) {
			$paths = array_merge($paths, $childQuery->filteredBasePaths());
		}
		if ($this->isReversed()) {
			$paths = $this->reversePaths($paths);
		}
		return $paths;
	}

	/**
	 * @param string[] $paths
	 * @return string[]
	 */
	public function reversePaths($paths) {
		return array_reverse($paths);
	}

	/**
	 * @return bool
	 */
	public function hasPaths() {
		$paths = $this->listPaths();
		return !empty($paths);
	}
} 