<?php

namespace FQ\Query;

use FQ\Dirs\ChildDir;
use FQ\Exceptions\FileException;
use FQ\Files;

class FilesQueryChild {

	/**
	 * @var FilesQuery
	 */
	private $filesQuery;

	/**
	 * @var ChildDir
	 */
	private $childDir;

	/**
	 * @var string
	 */
	private $rawRelativePath;

	/**
	 * @var string[]
	 */
	private $rawAbsolutePaths;

	/**
	 * @var string[]
	 */
	private $rawBasePaths;

	/**
	 * @var bool[]
	 */
	private $pathsExist;

	private $filteredPathsCashed;

	/**
	 * @var string[]
	 */
	private $filteredRawPaths;
	/**
	 * @var string[]
	 */
	private $filteredAbsolutePaths;
	/**
	 * @var string[]
	 */
	private $filteredBasePaths;

	/**
	 * @var string
	 */
	private $error;

	function __construct(FilesQuery $filesQuery, ChildDir $childDir) {
		$this->filesQuery = $filesQuery;
		$this->childDir = $childDir;
	}

	/**
	 * @return FilesQuery
	 */
	public function query() {
		return $this->filesQuery;
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
		return $this->childDir;
	}

	public function fileName() {
		return $this->query()->queriedFileName();
	}

	private function _error($error) {
		return $this->error = $error;
	}
	public function error() {
		return $this->error;
	}
	public function hasError() {
		return $this->error !== null;
	}

	public function relativePath() {
		if (empty($this->rawRelativePath)) {
			$childDir = $this->childDir();
			$fileName = $this->fileName();

			// add a file name extension if one isn't provided
			$fileNameExploded = explode('.', $fileName);
			if (count($fileNameExploded) == 1) {
				$fileName = $fileName . '.' . $childDir->defaultFileExtension();
			}

			$this->rawRelativePath = '/' . $childDir->dir() . '/' . $fileName;
		}
		return $this->rawRelativePath;
	}


	public function rawAbsolutePaths() {
		if (empty($this->rawAbsolutePaths)) {
			$this->rawAbsolutePaths = $this->_generatePaths('dir');
		}
		return $this->rawAbsolutePaths;
	}
	public function rawBasePaths() {
		if (empty($this->rawBasePaths)) {
			$this->rawBasePaths = $this->_generatePaths('basePath');
		}
		return $this->rawBasePaths;
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
		if (empty($this->filteredRawPaths)) {

			$filterCache = $this->_cachedFilterPaths();

			$paths = $this->relativePath();

			foreach ($filterCache as $filter) {
				foreach ($filter as $rootDirId => $isAllowed) {
					if ($isAllowed === false) unset($paths[$rootDirId]);
				}
			}

			$this->filteredRawPaths = $paths;
		}
		return $this->filteredRawPaths;
	}
	public function filteredAbsolutePaths() {
		if (empty($this->filteredAbsolutePaths)) {
			$this->filteredAbsolutePaths = $this->_filterPaths($this->rawAbsolutePaths());
		}
		return $this->filteredAbsolutePaths;
	}
	public function filteredBasePaths() {
		if (empty($this->filteredBasePaths)) {
			$this->filteredBasePaths = $this->_filterPaths($this->rawBasePaths());
		}
		return $this->filteredBasePaths;
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
		if (empty($this->pathsExist)) {

			$arr = array();
			foreach ($this->rawAbsolutePaths() as $rootDirId => $absolutePath) {
				$arr[$rootDirId] = file_exists($absolutePath);
			}
			$this->pathsExist = $arr;
		}
		return $this->pathsExist;
	}

	private function _cachedFilterPaths($filters = null) {
		if (empty($this->filteredPathsCashed)) {
			$filters = (array) ($filters === null ? $this->query()->getFilters() : $filters);

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
			$this->filteredPathsCashed = $filteredPaths;
		}
		return $this->filteredPathsCashed;
	}

	public function totalPathsExist() {
		return count(array_filter($this->pathsExist()));
	}

	public function meetsRequirements() {

		$pathsExist = $this->pathsExist();
		$totalPathsExist = $this->totalPathsExist();

		$error = null;

		$requiredLevelsArr = (array) $this->query()->requirements();
		foreach ($requiredLevelsArr as $requiredLevel) {
			switch ($requiredLevel) {
				case FilesQuery::LEVELS_NONE :
					break;
				case FilesQuery::LEVELS_ONE :
					if ($totalPathsExist == 0) {
						$error = sprintf('At least 1 file must be available for file "%s" in child with an id of "%s". Please create the file in any of these locations: %s', $this->relativePath(), $this->childDir()->id(), implode($this->rawAbsolutePaths()));
					}
					break;
				case FilesQuery::LEVELS_LAST :
					if ($totalPathsExist == 0 || $pathsExist[0] == null) {
						$error = sprintf('Last file "%s" not found in child "%s" but it is required', $this->relativePath(), $this->childDir()->id());
					}
					break;
				case FilesQuery::LEVELS_ALL :
					if ($this->totalPathsExist() != $this->files()->totalRootDirs()) {
						$error = sprintf('All "%s" children must contain a file called "%s".', $this->childDir()->id(), $this->relativePath());
					}
					break;
				default :
					$error = sprintf('Unknown requirement level "%s"', $requiredLevel);
					break;
			}
		}

		$this->_error($error);

		return $error === null;
	}
} 