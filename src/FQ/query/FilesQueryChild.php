<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Exceptions\FileQueryException;
use FQ\Files;
use FQ\Query\Selection\RootSelection;

class FilesQueryChild {

	/**
	 * @var FilesQuery
	 */
	private $_filesQuery;

	/**
	 * @var RootDir[]
	 */
	private $_rootDirs;

	/**
	 * @var ChildDir
	 */
	private $_childDir;

	/**
	 * @var string
	 */
	private $_rawRelativePath;

	/**
	 * @var string[]
	 */
	private $_rawAbsolutePaths;

	/**
	 * @var string[]
	 */
	private $_rawBasePaths;

	/**
	 * @var bool[]
	 */
	private $_pathsExist;

	/**
	 * @var array
	 */
	private $_filteredPathsCashed;

	/**
	 * @var string[]
	 */
	private $_filteredAbsolutePaths;
	/**
	 * @var string[]
	 */
	private $_filteredBasePaths;


	function __construct(FilesQuery $filesQuery, ChildDir $childDir) {
		$this->_filesQuery = $filesQuery;
		$this->_childDir = $childDir;
	}

	public function setRootDirs($dirs) {
		$this->_rootDirs = $dirs;
	}
	public function getRootDirs() {
		if ($this->_rootDirs === null) {
			return array();
		}
		return $this->_rootDirs;
	}

	/**
	 * Resets the FilesQueryChild so it can be reused
	 */
	public function reset() {
		$this->_rawRelativePath = null;
		$this->_rawAbsolutePaths = null;
		$this->_rawBasePaths = null;

		$this->_rootDirs = null;

		$this->_pathsExist = null;

		$this->_filteredPathsCashed = null;
		$this->_filteredBasePaths = null;
	}

	/**
	 * @return FilesQuery
	 */
	public function query() {
		return $this->_filesQuery;
	}

	/**
	 * @return Files
	 */
	public function files() {
		return $this->query()->files();
	}

	/**
	 * @return ChildDir
	 */
	public function childDir() {
		return $this->_childDir;
	}

	/**
	 * Returns the full string of the requested file within a child relative from any defined root directory
	 *
	 * @return string Relative child file path
	 */
	public function relativePath() {
		if (empty($this->_rawRelativePath)) {
			$childDir = $this->childDir();
			$fileName = $this->query()->queriedFileName();

			// add a file name extension if one isn't provided
			$fileNameExploded = explode('.', $fileName);
			if (count($fileNameExploded) == 1) {
				$fileName = $fileName . '.' . $childDir->defaultFileExtension();
			}

			$this->_rawRelativePath = '/' . $childDir->dir() . '/' . $fileName;
		}
		return $this->_rawRelativePath;
	}


	/**
	 * Generates all possible absolute paths from the configured root dirs
	 *
	 * @return string[] Array of all possible paths
	 */
	public function rawAbsolutePaths() {
		if (empty($this->_rawAbsolutePaths)) {
			$this->_rawAbsolutePaths = $this->_generatePaths('dir');
		}
		return $this->_rawAbsolutePaths;
	}

	/**
	 * Generates all possible base paths from the configured root dirs
	 *
	 * @return string[] Array of all possible paths
	 */
	public function rawBasePaths() {
		if (empty($this->_rawBasePaths)) {
			$this->_rawBasePaths = $this->_generatePaths('basePath');
		}
		return $this->_rawBasePaths;
	}

	/**
	 * Quick-and-dirty method to generate paths based a method name from a root directory
	 *
	 * @param $dirMethod
	 * @throws FileQueryException
	 * @return array
	 */
	private function _generatePaths($dirMethod) {
		$paths = array();
		$methodExists = null;

		foreach ($this->getRootDirs() as $rootDir) {
			if ($methodExists === null) {
				$methodExists = method_exists($rootDir, $dirMethod);
				if (!$methodExists) {
					throw new FileQueryException(sprintf('Cannot generate paths because method (%s) is not defined in %s', $dirMethod, get_class($rootDir)));
				}
			}
			$paths[$rootDir->id()] = $rootDir->$dirMethod() . $this->relativePath();
		}
		return $paths;
	}

	/**
	 * Method that returns all absolute paths after they have gone through the configured filters
	 *
	 * @return string[] Array of all the filtered absolute paths
	 */
	public function filteredAbsolutePaths() {
		if (empty($this->_filteredAbsolutePaths)) {
			$this->_filteredAbsolutePaths = $this->_filterPaths($this->rawAbsolutePaths());
		}
		return $this->_filteredAbsolutePaths;
	}

	/**
	 * Method that returns all base paths after they have gone through the configured filters
	 *
	 * @return string[] Array of all the filtered base paths
	 */
	public function filteredBasePaths() {
		if (empty($this->_filteredBasePaths)) {
			$this->_filteredBasePaths = $this->_filterPaths($this->rawBasePaths());
		}
		return $this->_filteredBasePaths;
	}

	/**
	 * This will filter a set of paths using the configured filters
	 *
	 * @param string[] $paths A collection of paths that need filtering
	 * @return string[] Filtered paths
	 */
	private function _filterPaths($paths) {
		foreach ($this->_cachedFilterPaths() as $filter) {
			foreach ($filter as $rootDirId => $isAllowed) {
				if ($isAllowed === false) unset($paths[$rootDirId]);
			}
		}
		return $paths;
	}

	/**
	 * An array that returns a complete mirror of all the possible paths but each entry contains a boolean to indicate
	 * if the path exists
	 *
	 * @return bool[] Mirror of the possible existing/non-existing paths
	 */
	public function pathsExist() {
		if (empty($this->_pathsExist)) {

			$arr = array();
			foreach ($this->rawAbsolutePaths() as $rootDirId => $absolutePath) {
				$arr[$rootDirId] = file_exists($absolutePath);
			}
			$this->_pathsExist = $arr;
		}
		return $this->_pathsExist;
	}

	/**
	 * This method will return a full overview of all the returns of the configured filters
	 *
	 * @return array
	 */
	private function _cachedFilterPaths() {
		if (empty($this->_filteredPathsCashed)) {
			$filters = $this->query()->filters();

			$rootDirs = $this->getRootDirs();
			$pathsExist = $this->pathsExist();

			$filteredPaths = array();
			foreach ($filters as $filter) {
				$filteredPaths[$filter] = array();

				foreach ($rootDirs as $rootDir) {
					$rootDirId = $rootDir->id();
					switch ($filter) {
						case FilesQuery::FILTER_NONE :
							$filteredPaths[$filter][$rootDirId] = true;
							break;
						case FilesQuery::FILTER_EXISTING :
							$filteredPaths[$filter][$rootDirId] = $pathsExist[$rootDirId];
							break;
					}
				}
			}
			$this->_filteredPathsCashed = $filteredPaths;
		}
		return $this->_filteredPathsCashed;
	}

	/**
	 * @return int Amount of existing paths within this child query
	 */
	public function totalExistingPaths() {
		return count(array_filter($this->pathsExist()));
	}
} 