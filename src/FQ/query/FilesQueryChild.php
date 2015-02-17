<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Exceptions\FileException;
use FQ\Files;
use FQ\Core\Exceptionable;

class FilesQueryChild extends Exceptionable {

	/**
	 * @var FilesQuery
	 */
	private $_filesQuery;

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

	/**
	 * Resets the FilesQueryChild so it can be reused
	 */
	public function reset() {
		$this->_rawRelativePath = null;
		$this->_rawAbsolutePaths = null;
		$this->_rawBasePaths = null;

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
	 * @return array
	 */
	private function _generatePaths($dirMethod) {
		$paths = array();
		$methodExists = null;
		foreach ($this->files()->rootDirs() as $rootDir) {
			if ($methodExists === null) {
				$methodExists = method_exists($rootDir, $dirMethod);
				if (!$methodExists) {
					$this->_throwError(sprintf('Cannot generate paths because method (%s) is not defined in %s', $dirMethod, get_class($rootDir)));
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

			$rootDirs = $this->files()->rootDirs();
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

	/**
	 * Checks if the query meets all its requirements
	 *
	 * @return bool
	 */
	public function meetsRequirements() {
		$requiredLevels = $this->query()->requirements();

		// if there are no requirements it certainly is valid and it can be returned immediately
		if (count($requiredLevels) === 0) {
			return true;
		}

		foreach ($requiredLevels as $requiredLevel) {
			if ($this->meetsRequirement($requiredLevel) === false) {
				return false;
			}
		}
		return true;
	}

	/**
	 * @param string $requirement
	 * @param bool $throwError
	 * @return bool
	 */
	public function meetsRequirement($requirement, $throwError = true) {
		switch ($requirement) {
			case FilesQuery::LEVELS_ONE :
				if ($this->totalExistingPaths() == 0) {
					return $this->_throwError(sprintf('At least 1 file must be available for file "%s" in child with an id of "%s". Please create the file in any of these locations: %s', $this->relativePath(), $this->childDir()->id(), implode($this->rawAbsolutePaths())), $throwError);
				}
				break;
			case FilesQuery::LEVELS_LAST :
				$pathsExist = $this->pathsExist();
				if ($this->totalExistingPaths() === 0 || $pathsExist[0] == null) {
					return $this->_throwError(sprintf('Last file "%s" not found in child "%s" but it is required', $this->relativePath(), $this->childDir()->id()), $throwError);
				}
				break;
			case FilesQuery::LEVELS_ALL :
				if ($this->totalExistingPaths() != $this->files()->totalRootDirs()) {
					return $this->_throwError(sprintf('All "%s" children must contain a file called "%s".', $this->childDir()->id(), $this->relativePath()), $throwError);
				}
				break;
			default :
				return $this->_throwError(sprintf('Unknown requirement level "%s"', $requirement), $throwError);
		}
		return true;
	}
} 