<?php

namespace FQ\Query;

use FQ\Exceptions\FileQueryBuilderException;
use FQ\Files;
use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\DirSelection;
use FQ\Query\Selection\RootSelection;

class FilesQueryBuilder  {

	private $_files;

	private $_fileName;
	private $_rootDirSelection;
	private $_childDirSelection;
	private $_requirements;
	private $_filters;
	private $_reverse;
	private $_reset;

	function __construct(Files $files) {
		$this->_childDirs = array();
		$this->_requirements = array();
		$this->_reset = true;

		$this->_files = $files;
	}

	protected function _files() {
		return $this->_files;
	}

	public function fileName($fileName) {
		$this->_fileName = $fileName;
		return $this;
	}
	protected function getFileName() {
		return $this->_fileName;
	}
	protected function _getRequirements() {
		return $this->_requirements;
	}
	protected function _isReversed() {
		return $this->_reverse === null ? false : $this->_reverse;
	}
	protected function _getFilters() {
		return $this->_filters;
	}

	public function includeRootDirs($rootDirs) {
		if (is_array($rootDirs)) {
			foreach ($rootDirs as $rootDir) {
				$this->_addToDirSelection(DirSelection::FILTER_INCLUDE, $this->rootSelection(), $rootDir);
			}
		}
		else {
			$this->_addToDirSelection(DirSelection::FILTER_INCLUDE, $this->rootSelection(), $rootDirs);
		}

		return $this;
	}
	public function excludeRootDirs($rootDirs) {
		if (is_array($rootDirs)) {
			foreach ($rootDirs as $rootDir) {
				$this->_addToDirSelection(DirSelection::FILTER_EXCLUDE, $this->rootSelection(), $rootDir);
			}
		}
		else {
			$this->_addToDirSelection(DirSelection::FILTER_EXCLUDE, $this->rootSelection(), $rootDirs);
		}

		return $this;
	}
	public function rootSelection() {
		if ($this->_rootDirSelection === null) {
			$this->_rootDirSelection = new RootSelection();
		}
		return $this->_rootDirSelection;
	}

	public function includeChildDirs($childDirs) {
		if (is_array($childDirs)) {
			foreach ($childDirs as $childDir) {
				$this->_addToDirSelection(DirSelection::FILTER_INCLUDE, $this->childSelection(), $childDir);
			}
		}
		else {
			$this->_addToDirSelection(DirSelection::FILTER_INCLUDE, $this->childSelection(), $childDirs);
		}

		return $this;
	}
	public function excludeChildDirs($childDirs) {
		if (is_array($childDirs)) {
			foreach ($childDirs as $childDir) {
				$this->_addToDirSelection(DirSelection::FILTER_EXCLUDE, $this->childSelection(), $childDir);
			}
		}
		else {
			$this->_addToDirSelection(DirSelection::FILTER_EXCLUDE, $this->childSelection(), $childDirs);
		}

		return $this;
	}
	public function childSelection() {
		if ($this->_childDirSelection === null) {
			$this->_childDirSelection = new ChildSelection();
		}
		return $this->_childDirSelection;
	}

	protected function _addToDirSelection($type, DirSelection $selectionObj, $dir) {
		switch ($type) {
			case DirSelection::FILTER_INCLUDE :
				if (is_string($dir)) {
					$selectionObj->includeDirById($dir);
				}
				else if (is_object($dir)) {
					$selectionObj->includeDir($dir);
				}
				break;
			case DirSelection::FILTER_EXCLUDE :
				if (is_string($dir)) {
					$selectionObj->excludeDirById($dir);
				}
				else if (is_object($dir)) {
					$selectionObj->excludeDir($dir);
				}
				break;
			default :
				throw new FileQueryBuilderException(sprintf('Add type of "%s" not found.', $type));
		}
	}




	public function addRequirement($requirement) {
		array_push($this->_requirements, $requirement);
		return $this;
	}

	public function reverse($reverse) {
		$this->_reverse = $reverse;
		return $this;
	}

	public function filters($filters = null) {
		$this->_filters = $filters;
		return $this;
	}

	public function run($fileName = null) {
		$query = $this->_files()->query($this->rootSelection(), $this->childSelection(), $this->_reset);
		$requirements = $query->requirements();
		$requirements->addRequirements($this->_getRequirements());
		$query->reverse($this->_isReversed());

		$filters = $this->_getFilters();
		if ($filters !== null) {
			$query->filters($filters);
		}

		$fileNameToUse = $fileName !== null ? $fileName : $this->getFileName();

		if (!is_string($fileNameToUse)) {
			throw new FileQueryBuilderException('No filename has been set. Use filename() to use a filename for the query or supply it this this run() method');
		}
		$query->run($fileNameToUse);

		return $query;
	}
}