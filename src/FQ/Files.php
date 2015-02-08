<?php

namespace FQ;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\FileException;
use FQ\Query\FilesQuery;

class Files {

	/**
	 * @var RootDir[] Array container all root directory locations
	 *
	 */
	private $_rootDirs;

	/**
	 * @var ChildDir[] Array container all child directories
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
		$this->_rootDirs = array();
		$this->_childDirs = array();
		$this->_query = new FilesQuery($this);
	}

	public function query($children = null, $reset = true) {
		$query = $this->_query;
		if ($reset) $query->reset();
		$query->addChildDirs($children);
		return $query;
	}

	/**
	 * @param RootDir $rootDir
	 * @param null|int $index
	 * @throws Exceptions\FileException
	 * @return RootDir
	 */
	public function addRootDir(RootDir $rootDir, $index = null) {
		if (is_int($index) && $this->totalRootDirs() < $index) {
			throw new FileException(sprintf('Provided index "%s" is to high. Total root dirs is %s', $index, $this->totalRootDirs()));
		}
		array_splice($this->_rootDirs, $index === null ? $this->totalRootDirs() - 1 : $index, 0, array($rootDir));
		$this->isValid();
		return $rootDir;
	}

	/**
	 * @return RootDir[]
	 */
	public function rootDirs() {
		return $this->_rootDirs;
	}

	/**
	 * @return int Total root directories
	 */
	public function totalRootDirs() {
		return count($this->_rootDirs);
	}

	/**
	 * @param string|RootDir $dir
	 * @return null|RootDir
	 */
	public function getRootDir($dir) {
		if (is_string($dir)) {
			$rootId = $this->getRootDirById($dir);
			if ($rootId !== null) {
				return $rootId;
			}
		}
		else if (is_object($dir)) {
			return $dir;
		}
		return null;
	}

	/**
	 * @param string $id
	 * @return null|RootDir
	 */
	public function getRootDirById($id) {
		foreach ($this->rootDirs() as $rootDir) {
			if ($rootDir->getId() == $id) return $rootDir;
		}
		return null;
	}

	/**
	 * @param int $index
	 * @return RootDir
	 */
	public function getRootDirByIndex($index) {
		return $this->_rootDirs[$index];
	}

	/**
	 * @return string[]
	 */
	public function getRootPaths() {
		$paths = array();
		foreach ($this->rootDirs() as $rootDir) {
			$paths[] = $rootDir->getPath();
		}
		return $paths;
	}

	/**
	 * @param ChildDir $childDir
	 * @param null|int $index
	 * @return ChildDir
	 */
	public function addChildDir(ChildDir $childDir, $index = null) {
		array_splice($this->_childDirs, $index === null ? $this->totalChildDirs() - 1 : $index, 0, array($childDir));
		$this->isValid();
		return $childDir;
	}

	/**
	 * @return ChildDir[]
	 */
	public function childDirs() {
		return $this->_childDirs;
	}

	/**
	 * @return int Total child directories
	 */
	public function totalChildDirs() {
		return count($this->_childDirs);
	}

	/**
	 * @param string|ChildDir $dir
	 * @return null|ChildDir
	 */
	public function getChildDir($dir) {
		if (is_string($dir)) {
			$childIndex = array_search($dir, $this->listChildDirNames());
			if ($childIndex !== false) {
				return $this->getChildDirByIndex($childIndex);
			}
		}
		else if (is_object($dir)) {
			return $dir;
		}
		return null;
	}

	/**
	 * @param int $index
	 * @return ChildDir
	 */
	public function getChildDirByIndex($index) {
		return $this->_childDirs[$index];
	}

	/**
	 * @return string[] Get a list of all the child's dir names
	 */
	public function listChildDirNames() {
		$children = array();
		foreach ($this->childDirs() as $child) {
			$children[] = $child->getDir();
		}
		return $children;
	}




	/**
	 * @param null|string|RootDir $rootDir
	 * @param null|string|ChildDir $childDir
	 * @return string
	 */
	public function getDirPath($rootDir, $childDir) {
		$rootDir = $this->getRootDir($rootDir);
		$child = $this->getChildDir($childDir);
		return $rootDir->getPath() . '/' . $child->getDir();
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
		$query = $this->simpleQuery($fileName, $children);
		return $query->listPaths();
	}

	/**
	 * @param string $fileName
	 * @param null $children
	 * @internal param null|string|\TB\Files\Core\ChildDir $childDir
	 * @return bool
	 */
	public function fileExists($fileName, $children = null) {
		$query = $this->simpleQuery($fileName, $children);
		return $query->hasPaths();
	}

	/**
	 * @param string $fileName
	 * @param null|ChildDir|ChildDir[] $children
	 * @return FilesQuery
	 */
	public function simpleQuery($fileName, $children = null) {
		$query = $this->query($children);
		$query->filters(FilesQuery::FILTER_EXISTING);
		$query->run($fileName);
		return $query;
	}

	protected function isValid() {
		foreach ($this->rootDirs() as $rootDir) {
			if ($rootDir->isRequired()) {
				$rootDirPath = $rootDir->getPath();
				if (!is_dir($rootDirPath)) {
					throw new FileException(sprintf('Root directory "%s" does not exist but is required. Please create this directory or turn this requirement off.', $rootDirPath));
				}
				foreach ($this->childDirs() as $childDir) {
					if ($childDir->isRequired()) {
						$childDirPath = $childDir->getDir();
						$childDirPathFull = $rootDirPath . '/' . $childDirPath;
						if (!is_dir($childDirPathFull)) {
							throw new FileException(sprintf('Child directory "%s" does not exist in root directory "%s". Please create the directory "%s" or turn this requirement off', $childDirPath, $rootDirPath, $childDirPathFull));
						}
					}
				}
			}
		}
	}

	/**
	 * Very simple require function. Ready to be extended when necessary.
	 *
	 * @param string $file
	 * @return bool
	 */
	public function requireOnce($file) {
		/** @noinspection PhpIncludeInspection */
		require_once $file;
		return true;
	}

	public function echoFiles($paths, $before = '', $after = '') {
		foreach ($paths as $path) {
			echo $before;
			readfile($path);
			echo $after;
		}
	}
} 