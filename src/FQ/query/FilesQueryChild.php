<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Exceptions\FileException;
use FQ\Files;

class FilesQueryChild {

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

	private $_filteredPathsCashed;

	/**
	 * @var string[]
	 */
	private $_filteredRawPaths;
	/**
	 * @var string[]
	 */
	private $_filteredAbsolutePaths;
	/**
	 * @var string[]
	 */
	private $_filteredBasePaths;

	/**
	 * @var string
	 */
	private $_error;

	private $_invalidated;

	function __construct(FilesQuery $filesQuery, ChildDir $childDir) {
		$this->_filesQuery = $filesQuery;
		$this->_childDir = $childDir;
	}

	public function reset() {
		$this->_error = null;


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

	public function fileName() {
		return $this->query()->queriedFileName();
	}

	private function _setError($error) {
		return $this->_error = $error;
	}
	public function error() {
		return $this->_error;
	}
	public function hasError() {
		return $this->_error !== null;
	}

	public function relativePath() {
		if (empty($this->_rawRelativePath)) {
			$childDir = $this->childDir();
			$fileName = $this->fileName();

			// add a file name extension if one isn't provided
			$fileNameExploded = explode('.', $fileName);
			if (count($fileNameExploded) == 1) {
				$fileName = $fileName . '.' . $childDir->defaultFileExtension();
			}

			$this->_rawRelativePath = '/' . $childDir->dir() . '/' . $fileName;
		}
		return $this->_rawRelativePath;
	}


	public function rawAbsolutePaths() {
		if (empty($this->_rawAbsolutePaths)) {
			$this->_rawAbsolutePaths = $this->_generatePaths('dir');
		}
		return $this->_rawAbsolutePaths;
	}
	public function rawBasePaths() {
		if (empty($this->_rawBasePaths)) {
			$this->_rawBasePaths = $this->_generatePaths('basePath');
		}
		return $this->_rawBasePaths;
	}
	private function _generatePaths($dirMethod) {
		$paths = array();
		$methodExists = null;
		foreach ($this->files()->rootDirs() as $rootDir) {
			if ($methodExists === null) {
				$methodExists = method_exists($rootDir, $dirMethod);
				if (!$methodExists) throw new FileException(sprintf('Cannot generate paths because method (%s) is not defined in %s', $dirMethod, get_class($rootDir)));
			}
			$paths[$rootDir->id()] = $rootDir->$dirMethod() . $this->relativePath();
		}
		return $paths;
	}


	public function filteredRawPaths() {
		if (empty($this->_filteredRawPaths)) {

			$filterCache = $this->_cachedFilterPaths();

			$paths = $this->relativePath();

			foreach ($filterCache as $filter) {
				foreach ($filter as $rootDirId => $isAllowed) {
					if ($isAllowed === false) unset($paths[$rootDirId]);
				}
			}

			$this->_filteredRawPaths = $paths;
		}
		return $this->_filteredRawPaths;
	}
	public function filteredAbsolutePaths() {
		if (empty($this->_filteredAbsolutePaths)) {
			$this->_filteredAbsolutePaths = $this->_filterPaths($this->rawAbsolutePaths());
		}
		return $this->_filteredAbsolutePaths;
	}
	public function filteredBasePaths() {
		if (empty($this->_filteredBasePaths)) {
			$this->_filteredBasePaths = $this->_filterPaths($this->rawBasePaths());
		}
		return $this->_filteredBasePaths;
	}
	private function _filterPaths($paths) {
		foreach ($this->_cachedFilterPaths() as $filter) {
			foreach ($filter as $rootDirId => $isAllowed) {
				if ($isAllowed === false) unset($paths[$rootDirId]);
			}
		}
		return $paths;
	}


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

	public function totalExistingPaths() {
		return count(array_filter($this->pathsExist()));
	}

	public function meetsRequirements() {

		$pathsExist = $this->pathsExist();
		$totalPathsExist = $this->totalExistingPaths();

		$error = null;

		$requiredLevelsArr = $this->query()->requirements();
		foreach ($requiredLevelsArr as $requiredLevel) {
			switch ($requiredLevel) {
				case FilesQuery::LEVELS_NONE :
					break;
				case FilesQuery::LEVELS_ONE :
					if ($totalPathsExist == 0) {
						$error = sprintf('At least 1 file must be available for file "%s" in child with an id of "%s". Please create the file in any of these locations: %s', $this->relativePath(), $this->childDir()->id(), implode($this->rawAbsolutePaths()));
						break 2;
					}
					break;
				case FilesQuery::LEVELS_LAST :
					if ($totalPathsExist == 0 || $pathsExist[0] == null) {
						$error = sprintf('Last file "%s" not found in child "%s" but it is required', $this->relativePath(), $this->childDir()->id());
						break 2;
					}
					break;
				case FilesQuery::LEVELS_ALL :
					if ($this->totalExistingPaths() != $this->files()->totalRootDirs()) {
						$error = sprintf('All "%s" children must contain a file called "%s".', $this->childDir()->id(), $this->relativePath());
						break 2;
					}
					break;
				default :
					$error = sprintf('Unknown requirement level "%s"', $requiredLevel);
					break 2;
			}
		}

		$this->_setError($error);

		return $error === null;
	}
} 