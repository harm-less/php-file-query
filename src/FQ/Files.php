<?php

namespace FQ;

use FQ\Collections\ChildDirCollection;
use FQ\Collections\RootDirCollection;
use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\FilesException;
use FQ\Query\FilesQuery;

class Files {

	/**
	 * @var RootDirCollection Container with all root directories
	 *
	 */
	private $_rootDirs;

	/**
	 * @var ChildDirCollection Container with all child directories
	 *
	 */
	private $_childDirs;

	/**
	 * @var FilesQuery Query object for each Files instance
	 *
	 */
	private $_query;


	const DEFAULT_EXTENSION = 'php';

	function __construct() {
		$this->_rootDirs = new RootDirCollection();
		$this->_childDirs = new ChildDirCollection();
		$this->_query = new FilesQuery($this);
	}

	/**
	 * @return RootDirCollection
	 */
	protected function _rootDirs() {
		return $this->_rootDirs;
	}

	/**
	 * @param RootDir $rootDir
	 * @param null|int $index
	 * @return RootDir|false
	 */
	public function addRootDir(RootDir $rootDir, $index = null) {
		$rootDir = $this->_rootDirs()->addRootDir($rootDir, $index);
		return $this->isValid() === true ? $rootDir : false;
	}

	/**
	 * @return RootDir[]
	 */
	public function rootDirs() {
		return $this->_rootDirs()->dirs();
	}

	/**
	 * @param RootDir $rootDir RootDir that will be checked
	 * @return bool Returns true if RootDir is part of this files instance
	 */
	public function isRootDirOf(RootDir $rootDir) {
		return $this->_rootDirs()->isInCollection($rootDir);
	}

	/**
	 * @return int Total root directories
	 */
	public function totalRootDirs() {
		return $this->_rootDirs()->totalDirs();
	}

	/**
	 * @param string|RootDir $rootDir
	 * @return null|RootDir
	 */
	public function getRootDir($rootDir) {
		return $this->_rootDirs()->getDir($rootDir);
	}

	/**
	 * @param string $id
	 * @return null|RootDir
	 */
	public function getRootDirById($id) {
		return $this->_rootDirs()->getDirById($id);
	}

	/**
	 * @param int $index
	 * @return RootDir|false
	 */
	public function getRootDirByIndex($index) {
		return $this->_rootDirs()->getDirByIndex($index);
	}

	/**
	 * @return string[]
	 */
	public function getRootPaths() {
		return $this->_rootDirs()->getPaths();
	}



	/**
	 * @return ChildDirCollection
	 */
	protected function _childDirs() {
		return $this->_childDirs;
	}

	/**
	 * @param ChildDir $childDir
	 * @param null|int $index
	 * @return ChildDir|false
	 */
	public function addChildDir(ChildDir $childDir, $index = null) {
		$childDir = $this->_childDirs()->addChildDir($childDir, $index);
		return $this->isValid() === true ? $childDir : false;
	}

	/**
	 * @return ChildDir[]
	 */
	public function childDirs() {
		return $this->_childDirs()->dirs();
	}

	/**
	 * @param ChildDir $childDir Dir that will be checked
	 * @return bool Returns true if dir is part of this files instance
	 */
	public function isChildDirOf(ChildDir $childDir) {
		return $this->_childDirs()->isInCollection($childDir);
	}

	/**
	 * @return int Total child directories
	 */
	public function totalChildDirs() {
		return $this->_childDirs()->totalDirs();
	}

	/**
	 * @param string|ChildDir $childDir
	 * @return null|ChildDir
	 */
	public function getChildDir($childDir) {
		return $this->_childDirs()->getDir($childDir);
	}

	/**
	 * @param string $id
	 * @return null|ChildDir
	 */
	public function getChildDirById($id) {
		return $this->_childDirs()->getDirById($id);
	}

	/**
	 * @param int $index
	 * @return ChildDir|false
	 */
	public function getChildDirByIndex($index) {
		return $this->_childDirs()->getDirByIndex($index);
	}

	/**
	 * @return string[]
	 */
	public function getChildPaths() {
		return $this->_childDirs()->getPaths();
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
					throw new FilesException(sprintf('Root directory "%s" does not exist but is required. Please create this directory or turn this requirement off.', $rootDirPath));
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
	 * @param null|string|RootDir $rootDir
	 * @param null|string|ChildDir $childDir
	 * @return string
	 */
	public function getFullPath($rootDir, $childDir) {
		$rootDir = $this->getRootDir($rootDir);
		$child = $this->getChildDir($childDir);
		return $rootDir->dir() . '/' . $child->dir();
	}

	/**
	 * @param string $fileName
	 * @param null|string|ChildDir[] $children
	 * @param bool $reverseLoad
	 * @return null|string
	 */
	public function filePath($fileName, $children = null, $reverseLoad = true) {
		$query = $this->query($children);
		$query->reverse($reverseLoad);
		$query->requirements(FilesQuery::LEVELS_ONE);
		$query->run($fileName);

		$paths = $query->listPaths();
		if (count($paths)) {
			foreach ($paths as $path) return $path;
		}
		return false;
	}

	/**
	 * @param string $fileName
	 * @param null|string|ChildDir[] $children
	 * @param bool $reverseLoad
	 * @return bool
	 */
	public function loadFile($fileName, $children = null, $reverseLoad = true) {
		$path = $this->filePath($fileName, $children, $reverseLoad);
		if ($path) {
			return $this->requireOnce($path);
		}
		return false;
	}

	/**
	 * @param string $fileName
	 * @param null|string|ChildDir[] $children
	 * @param string $requiredLevels
	 * @param bool $reverseLoad
	 * @return bool
	 */
	public function loadFiles($fileName, $children = null, $requiredLevels = FilesQuery::LEVELS_ONE, $reverseLoad = true) {
        $query = $this->query($children);
        $query->reverse($reverseLoad);
        $query->requirements($requiredLevels);
        $query->run($fileName);

		foreach ($query->listPaths() as $file) {
			$this->requireOnce($file);
		}
        return true;
	}

	/**
	 * @param string $fileName
	 * @param null|string|ChildDir[] $children
	 * @return FilesQuery
	 */
	public function getFilePaths($fileName, $children = null) {
		$query = $this->querySimple($fileName, $children);
		return $query->listPaths();
	}

	/**
	 * @param string $fileName
	 * @param null $children
	 * @internal param null|string|ChildDir $childDir
	 * @return bool
	 */
	public function fileExists($fileName, $children = null) {
		$query = $this->querySimple($fileName, $children);
		return $query->hasPaths();
	}

	/**
	 * @param null|ChildDir|ChildDir[] $children
	 * @param bool $reset
	 * @return FilesQuery
	 */
	public function query($children = null, $reset = true) {
		$query = $this->_query;
		if ($reset) $query->reset();
		$query->addChildDirs($children);
		return $query;
	}

	/**
	 * A basic query wrapper
	 *
	 * @param string $fileName
	 * @param null|ChildDir|ChildDir[] $children
	 * @param bool $reset
	 * @return FilesQuery
	 */
	public function querySimple($fileName, $children = null, $reset = true) {
		$query = $this->query($children, $reset);
		$query->run($fileName);
		return $query;
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