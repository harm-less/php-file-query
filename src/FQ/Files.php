<?php

namespace FQ;

use FQ\Collections\ChildDirCollection;
use FQ\Collections\RootDirCollection;
use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\FilesException;
use FQ\Query\FilesQuery;
use FQ\Query\FilesQueryRequirements;
use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\RootSelection;

class Files {

	/**
	 * @var RootDirCollection Container with all root directories
	 *
	 */
	private $_rootDirCollection;

	/**
	 * @var ChildDirCollection Container with all child directories
	 *
	 */
	private $_childDirCollection;

	/**
	 * @var FilesQuery Query object for each Files instance
	 *
	 */
	private $_query;

	const DEFAULT_EXTENSION = 'php';

	function __construct() {
		$this->_rootDirCollection = new RootDirCollection();
		$this->_childDirCollection = new ChildDirCollection();
		$this->_query = new FilesQuery($this);
	}

	/**
	 * @return RootDirCollection
	 */
	protected function _rootCollection() {
		return $this->_rootDirCollection;
	}

	/**
	 * @param RootDir $rootDir
	 * @param null|int $index
	 * @return RootDir|false
	 */
	public function addRootDir(RootDir $rootDir, $index = null) {
		$rootDir = $this->_rootCollection()->addRootDir($rootDir, $index);
		return $this->isValid() === true ? $rootDir : false;
	}

	/**
	 * @param RootDir $rootDir
	 * @return bool
	 */
	public function removeRootDir(RootDir $rootDir) {
		return $this->_rootCollection()->removeDir($rootDir);
	}
	/**
	 * @param string $id
	 * @return bool
	 */
	public function removeRootDirById($id) {
		return $this->_rootCollection()->removeDirById($id);
	}
	/**
	 * @param int $index
	 * @return bool
	 */
	public function removeRootDirAtIndex($index) {
		return $this->_rootCollection()->removeDirAtIndex($index);
	}
	public function removeAllRootDirs() {
		$this->_rootCollection()->removeAllDirs();
	}

	/**
	 * @return RootDir[]
	 */
	public function rootDirs() {
		return $this->_rootCollection()->dirs();
	}

	/**
	 * @param RootDir $rootDir RootDir that will be checked
	 * @return bool Returns true if RootDir is part of this files instance
	 */
	public function containsRootDir(RootDir $rootDir) {
		return $this->_rootCollection()->isInCollection($rootDir);
	}

	/**
	 * @return int Total root directories
	 */
	public function totalRootDirs() {
		return $this->_rootCollection()->totalDirs();
	}

	/**
	 * @param string|RootDir $rootDir
	 * @return RootDir|null
	 */
	public function getRootDir($rootDir) {
		return $this->_rootCollection()->getDir($rootDir);
	}

	/**
	 * @param string $id
	 * @return null|RootDir
	 */
	public function getRootDirById($id) {
		return $this->_rootCollection()->getDirById($id);
	}

	/**
	 * @param int $index
	 * @return RootDir|false
	 */
	public function getRootDirByIndex($index) {
		return $this->_rootCollection()->getDirByIndex($index);
	}

	/**
	 * @return string[]
	 */
	public function getRootPaths() {
		return $this->_rootCollection()->getPaths();
	}



	/**
	 * @return ChildDirCollection
	 */
	protected function _childCollection() {
		return $this->_childDirCollection;
	}

	/**
	 * @param ChildDir $childDir
	 * @param null|int $index
	 * @return ChildDir|false
	 */
	public function addChildDir(ChildDir $childDir, $index = null) {
		$childDir = $this->_childCollection()->addChildDir($childDir, $index);
		return $this->isValid() === true ? $childDir : false;
	}

	/**
	 * @param ChildDir $childDir
	 * @return bool
	 */
	public function removeChildDir(ChildDir $childDir) {
		return $this->_childCollection()->removeDir($childDir);
	}
	/**
	 * @param string $id
	 * @return bool
	 */
	public function removeChildDirById($id) {
		return $this->_childCollection()->removeDirById($id);
	}
	/**
	 * @param int $index
	 * @return bool
	 */
	public function removeChildDirAtIndex($index) {
		return $this->_childCollection()->removeDirAtIndex($index);
	}
	public function removeAllChildDirs() {
		$this->_childCollection()->removeAllDirs();
	}

	/**
	 * @return ChildDir[]
	 */
	public function childDirs() {
		return $this->_childCollection()->dirs();
	}

	/**
	 * @param ChildDir $childDir Dir that will be checked
	 * @return bool Returns true if dir is part of this files instance
	 */
	public function containsChildDir(ChildDir $childDir) {
		return $this->_childCollection()->isInCollection($childDir);
	}

	/**
	 * @return int Total child directories
	 */
	public function totalChildDirs() {
		return $this->_childCollection()->totalDirs();
	}

	/**
	 * @param string|ChildDir $childDir
	 * @return null|ChildDir
	 */
	public function getChildDir($childDir) {
		return $this->_childCollection()->getDir($childDir);
	}

	/**
	 * @param string $id
	 * @return null|ChildDir
	 */
	public function getChildDirById($id) {
		return $this->_childCollection()->getDirById($id);
	}

	/**
	 * @param int $index
	 * @return ChildDir|false
	 */
	public function getChildDirByIndex($index) {
		return $this->_childCollection()->getDirByIndex($index);
	}

	/**
	 * @return string[]
	 */
	public function getChildPaths() {
		return $this->_childCollection()->getPaths();
	}

	/**
	 * Checks if the children of the object are still valid
	 *
	 * @throws FilesException Thrown when object is not valid
	 */
	protected function isValid() {
		foreach ($this->rootDirs() as $rootDir) {
			if ($rootDir->isRequired()) {
				$rootDirPath = $rootDir->dir();
				if (!is_dir($rootDirPath)) {
					throw new FilesException(sprintf('Absolute root directory "%s" does not exist but is required. Please create this directory or turn this requirement off.', $rootDirPath));
				}
				foreach ($this->childDirs() as $childDir) {
					if ($childDir->isRequired()) {
						$fullPath = $this->getFullPath($rootDir, $childDir);
						if (!is_dir($fullPath)) {
							throw new FilesException(sprintf('Child directory "%s" does not exist in root directory "%s". Please create the directory "%s" or turn this requirement off', $childDir->dir(), $rootDirPath, $fullPath));
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param string|RootDir $rootDir
	 * @param string|ChildDir $childDir
	 * @return string
	 */
	public function getFullPath($rootDir, $childDir) {
		$rootDirObj = $this->getRootDir($rootDir);
		$childObj = $this->getChildDir($childDir);

		if ($rootDirObj === null || $childObj === null) {
			$rootDirId = $rootDirObj !== null ? $rootDirObj->id() : '-';
			$childDirId = $childObj !== null ? $childObj->id() : '-';
			throw new FilesException(sprintf('Cannot build a full path because either the root directory, the child directory or both are not defined. Root directory id "%s". Child directory id "%s"', $rootDirId, $childDirId));
		}
		return $childObj->fullAbsolutePath($rootDirObj);
	}

	/**
	 * @param string $fileName
	 * @param ChildSelection $childSelection
	 * @param RootSelection $rootSelection
	 * @param bool $reverseLoad
	 * @return false|string
	 */
	public function queryPath($fileName, ChildSelection $childSelection = null, RootSelection $rootSelection = null, $reverseLoad = false) {
		$query = $this->query($rootSelection, $childSelection, true, true);
		$query->reverse($reverseLoad);
		$query->requirements(FilesQueryRequirements::REQUIRE_ONE);
		if ($query->run($fileName)) {
			$paths = $query->listPaths();
			if (count($paths)) foreach ($paths as $path) return $path;
		}
		return false;
	}

	/**
	 * @param string $fileName
	 * @param ChildSelection $childSelection
	 * @param RootSelection $rootSelection
	 * @param bool $reverseLoad
	 * @return bool
	 */
	public function loadFile($fileName, ChildSelection $childSelection = null, RootSelection $rootSelection = null, $reverseLoad = false) {
		$path = $this->queryPath($fileName, $childSelection, $rootSelection, $reverseLoad);
		if ($path) {
			return $this->requireOnce($path);
		}
		return false;
	}

	/**
	 * @param string $fileName
	 * @param RootSelection $rootDirs
	 * @param ChildSelection $children
	 * @param string $requiredLevels
	 * @param bool $reverseLoad
	 * @return bool
	 */
	public function loadFiles($fileName, RootSelection $rootDirs = null, ChildSelection $children = null, $requiredLevels = FilesQueryRequirements::REQUIRE_ONE, $reverseLoad = false)
	{
		$query = $this->query($rootDirs, $children, true, true);
		$query->reverse($reverseLoad);
		$query->requirements($requiredLevels);
		if ($query->run($fileName)) {
			$paths = $query->listPaths();
			if (count($paths)) {
				foreach ($query->listPaths() as $file) {
					$this->includeOnce($file);
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $fileName
	 * @param RootSelection $rootDirs
	 * @param ChildSelection $children
	 * @return array
	 */
	public function queryPaths($fileName, RootSelection $rootDirs = null, ChildSelection $children = null) {
		$query = $this->query($rootDirs, $children, true, true);
		$query->run($fileName);
		return $query->listPaths();
	}

	/**
	 * @param string $fileName
	 * @param RootSelection $rootDirs
	 * @param ChildSelection $children
	 * @return bool
	 */
	public function fileExists($fileName, RootSelection $rootDirs = null, ChildSelection $children = null) {
		$query = $this->query($rootDirs, $children, true, true);
		$query->run($fileName);
		return $query->hasPaths();
	}

	/**
	 * @param RootSelection $rootDirs
	 * @param ChildSelection $children
	 * @param bool $resetQuery
	 * @param bool $resetSelection
	 * @return FilesQuery
	 */
	public function query(RootSelection $rootDirs = null, ChildSelection $children = null, $resetQuery = true, $resetSelection = false) {
		$query = $this->_query;
		if ($resetQuery) {
			$query->reset();
		}
		if ($resetSelection) {
			$query->resetSelection();
		}
		if ($rootDirs) $query->setRootDirSelection($rootDirs);
		if ($children) $query->setChildDirSelection($children);
		return $query;
	}

	/**
	 * Very simple include function wrapper. Ready to be extended when necessary.
	 *
	 * @param string $file
	 * @return bool
	 */
	protected function includeOnce($file) {
		/** @noinspection PhpIncludeInspection */
		include_once $file;
		return true;
	}

	/**
	 * Very simple require function wrapper. Ready to be extended when necessary.
	 *
	 * @param string $file
	 * @return bool
	 */
	protected function requireOnce($file) {
		/** @noinspection PhpIncludeInspection */
		require_once $file;
		return true;
	}
} 