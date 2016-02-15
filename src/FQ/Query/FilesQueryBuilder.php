<?php

namespace FQ\Query;

use FQ\Exceptions\FileQueryBuilderException;
use FQ\Query\Selection\DirSelection;
use FQ\Query\Selection\RootSelection;
use FQ\Query\Selection\ChildSelection;

class FilesQueryBuilder  {

	private $_query;

	private $_fileName;
	/**
	 * @var RootSelection
	 */
	private $_rootDirSelection;
	/**
	 * @var ChildSelection
	 */
	private $_childDirSelection;
	private $_requirements;
	private $_filters;
	private $_reverse;
	private $_showErrors;
	private $_resetQuery;

	private $_isPrepared;

	function __construct(FilesQuery $query) {
		$this->_query = $query;

		$this->reset();
	}

	protected function _query() {
		return $this->_query;
	}

	public function fileName($fileName) {
		$this->_fileName = $fileName;
		return $this;
	}
	public function getFileName() {
		return $this->_fileName;
	}

	public function resetsQuery() {
		return $this->_resetQuery === null ? true : $this->_resetQuery;
	}
	public function getRequirements() {
		return $this->_requirements;
	}
	public function isReversed() {
		return $this->_reverse === null ? false : $this->_reverse;
	}
	public function getFilters() {
		return $this->_filters;
	}
	public function showsErrors() {
		return $this->_showErrors === null ? true : $this->_showErrors;
	}

	public function includeRootDirs($rootDirs) {
		if ($rootDirs === null) {
			// don't include anything
			return $this;
		}

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
		if ($rootDirs === null) {
			// don't exclude anything
			return $this;
		}

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

	/**
	 * @return RootSelection
	 */
	public function rootSelection() {
		if ($this->_rootDirSelection === null) {
			$this->_rootDirSelection = new RootSelection();
		}
		return $this->_rootDirSelection;
	}
	/**
	 * @return bool
	 */
	protected function _hasActiveRootSelection() {
		return $this->_rootDirSelection !== null;
	}

	public function includeChildDirs($childDirs) {
		if ($childDirs === null) {
			// don't include anything
			return $this;
		}

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
		if ($childDirs === null) {
			// don't exclude anything
			return $this;
		}

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

	/**
	 * @return ChildSelection
	 */
	public function childSelection() {
		if ($this->_childDirSelection === null) {
			$this->_childDirSelection = new ChildSelection();
		}
		return $this->_childDirSelection;
	}
	/**
	 * @return bool
	 */
	protected function _hasActiveChildSelection() {
		return $this->_childDirSelection !== null;
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

	public function filters($filters) {
		$this->_filters = $filters;
		return $this;
	}

	public function showErrors($showErrors) {
		$this->_showErrors = $showErrors;
		return $this;
	}

	public function reset() {
		if ($this->_childDirSelection !== null) {
			$this->_childDirSelection->reset();
		}
		if ($this->_rootDirSelection !== null) {
			$this->_rootDirSelection->reset();
		}
		$this->_filters = null;
		$this->_fileName = null;
		$this->_isPrepared = null;
		$this->_reverse = null;
		$this->_showErrors = null;
		$this->_requirements = array();
		return $this;
	}

	public function run($fileName = null) {

		$query = $this->prepare();

		$fileNameToUse = $fileName !== null ? $fileName : $this->getFileName();
		if (!is_string($fileNameToUse)) {
			throw new FileQueryBuilderException('No filename has been set. Use filename() to use a filename for the query or supply it this this run() method');
		}

		$result = $query->run($fileNameToUse);
		if ($result !== true && $this->showsErrors()) {
			throw $query->queryError();
		}
		return $query;
	}

	public function prepare() {
		$query = $this->_query();

		// reset the query if that is necessary
		if($this->resetsQuery()) {
			$query->reset();
		}

		// add the requirements to the query
		$requirements = $query->requirements();
		$requirements->addRequirements($this->getRequirements());

		// reverse the query if necessary
		$query->reverse($this->isReversed());

		// apply the set filters to the query
		$filters = $this->getFilters();
		if ($filters !== null) {
			$query->filters($filters);
		}

		if ($this->_hasActiveRootSelection() !== null) {
			$query->setRootDirSelection($this->rootSelection());
		}
		if ($this->_hasActiveChildSelection() !== null) {
			$query->setChildDirSelection($this->childSelection());
		}

		$this->_isPrepared = true;
		return $query;
	}
}