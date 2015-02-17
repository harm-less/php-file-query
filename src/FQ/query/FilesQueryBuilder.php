<?php

namespace FQ\Query;

use FQ\Files;

class FilesQueryBuilder  {

	private $_fileName;
	private $_childDirs;
	private $_requirements;
	private $_filters;
	private $_reverse;
	private $_reset;

	private $_files;
	function __construct(Files $files) {
		$this->_childDirs = array();
		$this->_requirements = array();
		$this->_reset = true;

		$this->_files = $files;
	}

	protected function _files() {
		return $this->_files;
	}

	protected function _query($children, $reset) {
		return $this->_files()->query($children, $reset);
	}

	public function fileName($fileName) {
		$this->_fileName = $fileName;
		return $this;
	}

	public function addChildDir($childDir) {
		array_push($this->_childDirs, $childDir);
		return $this;
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

	public function run($fileName) {
		$query = $this->_query($this->_childDirs, $this->_reset);
		$query->run($fileName);

		return $query;
	}
}