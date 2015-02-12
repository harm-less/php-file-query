<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Exceptions\FileException;
use FQ\Files;

class FilesQuery {

	/**
	 * @var Files
	 */
	private $files;

	/**
	 * @var ChildDir[]
	 */
	private $childDirs;

	/**
	 * @var FilesQueryChild[]
	 */
	private $queryChildDirs;

	/**
	 * @var bool Indicator if the query have been run or not
	 */
	private $hasRun;

	/**
	 * @var string Indicator if the query have been run or not
	 */
	private $queriedFileName;

	/**
	 * @var bool Reverse the query
	 */
	private $reverse;

	private $filters;

	/**
	 * @var bool When set to true, the query will throw an error if one occurs
	 */
	private $throwErrors;

	private $requirements;

	/**
	 * Constants determining requirement checking for a query
	 */
	const LEVELS_NONE = 'levels_none';
	const LEVELS_ONE = 'levels_one';
	const LEVELS_LAST = 'levels_last';
	const LEVELS_ALL = 'levels_all';

	/**
	 * Constants determining preliminary filters types
	 */
	const FILTER_NONE = 'filter_none';
	const FILTER_EXISTING = 'filter_existing';

	/**
	 * @param Files $files
	 * @param null|string|string[]|ChildDir|ChildDir[] $childDirs
	 */
	function __construct(Files $files, $childDirs = null) {
		$this->files = $files;

		//$this->addChildDirs($childDirs);

		$this->reset();
	}

	public function reset() {
		$this->childDirs = array();
		$this->queryChildDirs = array();

		$this->queriedFileName = null;
		$this->throwErrors = true;
		$this->requirements = self::LEVELS_ONE;
		$this->filters = self::FILTER_EXISTING;
		$this->reverse = false;

		$this->hasRun = false;
	}

	/**
	 * @param null|string|string[] $requirements
	 * @return null|string[]
	 */
	public function requirements($requirements = null) {
		if ($requirements !== null) {
			$this->requirements = $requirements;
		}
		return $this->requirements !== null ? (array) $this->requirements : null;
	}

	/**
	 * @param bool $reverse Reverse query results
	 */
	public function reverse($reverse) {
		$this->reverse = $reverse;
	}

	/**
	 * @param string|string[] $filters
	 */
	public function filters($filters = self::FILTER_EXISTING) {
		$this->filters = (is_string($filters) ? (array) $filters : $filters);
	}

	/**
	 * @param string $fileName The name of the file the query will be executing
	 * @return null|string[]
	 * @throws FileException
	 */
	public function run($fileName) {
		$this->queriedFileName = $fileName;

		$this->queryChildDirs = array();

		foreach ($this->childDirs() as $childDir) {

			$queryChild = new FilesQueryChild($this, $childDir);

			if (!$queryChild->meetsRequirements() && $this->throwErrors()) {
				throw new FileException($queryChild->error());
			}

			$this->queryChildDirs[] = $queryChild;
		}

		$this->hasRun = true;

		return $this->listPaths();
	}

	/**
	 * @param ChildDir $childDir
	 * @throws FileException
	 */
	public function addChildDir(ChildDir $childDir) {
		if (!$this->isValidChildDir($childDir)) {
			throw new FileException('Child dir is not part of the Files instance provided to this query');
		}
		array_push($this->childDirs, $childDir);
	}

	/**
	 * @param null|ChildDir[] $childDirs
	 */
	public function addChildDirs($childDirs = null) {
		$childDirs = ($childDirs === null ? $this->files->childDirs() : (is_array($childDirs) ? $childDirs : array($childDirs)));
		foreach ($childDirs as $childDir) {
			$this->addChildDir($this->files->getChildDir($childDir));
		}
	}

	/**
	 * @param ChildDir $childDir
	 * @return bool If a ChildDir is provided, it will check if it is part of the Files instance
	 */
	public function isValidChildDir(ChildDir $childDir) {
		return $childDir !== null && in_array($childDir, $this->files()->childDirs());
	}

	public function queriedFileName() {
		return $this->queriedFileName;
	}

	public function queryChildDirs() {
		return $this->queryChildDirs;
	}

	public function childDirs() {
		return $this->childDirs;
	}

	public function files() {
		return $this->files;
	}

	public function rootDirs() {
		return $this->files()->rootDirs();
	}

	public function hasRun() {
		return (bool) $this->hasRun;
	}
	public function isReversed() {
		return (bool) $this->reverse;
	}
	public function hasRequirements() {
		$requirements = $this->requirements();
		return (bool) $this->requirements !== null && $this->requirements !== self::LEVELS_NONE && (is_array($requirements) && count($requirements) == 1 && $requirements[0] !== self::LEVELS_NONE);
	}
	public function throwErrors() {
		return (bool) $this->throwErrors;
	}

	public function _hasRun() {
		if (!$this->hasRun()) {
			throw new FileException('You must first call the "run" method before you can retrieve query information');
		}
	}

	/**
	 * @return string[]
	 */
	public function getFilters() {
		return $this->filters;
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return null|string[]
	 */
	public function listPaths() {
		$this->_hasRun();

		$paths = array();
		foreach($this->queryChildDirs() as $childQuery) {
			$paths = array_merge($paths, $childQuery->filteredAbsolutePaths());
		}
		return $paths;
	}

	/**
	 * Returns a list of all the paths it found
	 *
	 * @return string[]
	 */
	public function listBasePaths() {
		$this->_hasRun();

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