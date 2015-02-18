<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Exceptions\FileQueryException;
use FQ\Files;

class FilesQuery {

	/**
	 * @var Files
	 */
	private $_files;

	/**
	 * @var ChildDir[]
	 */
	private $_childDirs;

	/**
	 * @var FilesQueryChild[]
	 */
	private $_cachedQueryChildren;

	/**
	 * @var FilesQueryChild[]
	 */
	private $_currentQueryChildren;

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
		$this->_childDirs = array();
		$this->_currentQueryChildren = array();

		$this->requirements()->removeAll();
		$this->filters(self::FILTER_EXISTING);

		$this->_queriedFileName = null;
		$this->_reverse = false;
		$this->_hasRun = false;
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
	 * @return string[]
	 */
	public function filters($filters = null) {
		if ($filters !== null) {
			$this->_filters = is_string($filters) ? (array) $filters : $filters;
		}
		return $this->_filters;
	}

	/**
	 * @param ChildDir $childDir
	 * @throws FileQueryException
	 * @return false|ChildDir
	 */
	public function addChildDir(ChildDir $childDir) {
		if (!$this->isValidChildDir($childDir)) {
			throw new FileQueryException('Child dir is not part of the Files instance provided to this query');
		}
		array_push($this->_childDirs, $childDir);
		return $childDir;
	}

	/**
	 * @param null|string|string[]|ChildDir|ChildDir[] $childDirs
	 */
	public function addChildDirs($childDirs = null) {
		$childDirs = ($childDirs === null || empty($childDirs) ? $this->_files->childDirs() : (is_array($childDirs) ? $childDirs : array($childDirs)));
		foreach ($childDirs as $childDir) {
			$this->addChildDir($this->_files->getChildDir($childDir));
		}
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

	public function childDirs() {
		return $this->_childDirs;
	}

	public function files() {
		return $this->_files;
	}

	public function rootDirs() {
		return $this->files()->rootDirs();
	}

	public function hasRun() {
		return $this->_hasRun;
	}
	public function isReversed() {
		return $this->_reverse;
	}

	public function _hasRun() {
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
		$this->_currentQueryChildren = array();
		foreach ($this->childDirs() as $childDir) {
			$this->_currentQueryChildren[$childDir->id()] = $this->processQueryChild($childDir);
		}
		$this->_hasRun = true;
		return $this->listPaths();
	}

	protected function processQueryChild(ChildDir $childDir) {
		$queryChild = $this->_getQueryChild($childDir);
		$queryChild->reset();
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
				$paths = array_merge($paths, $childQuery->filteredAbsolutePaths());
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