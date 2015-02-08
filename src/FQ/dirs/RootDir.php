<?php

namespace FQ\Dirs;

class RootDir extends Dir {

	private $basePath;

	function __construct($id, $absolutePath, $basePath = null, $required = true) {
		parent::__construct($absolutePath, $required);

		$this->setId($id);
		$this->setBasePath($basePath === null ? $absolutePath : $basePath);
	}

	/**
	 * @param string $path
	 */
	public function setBasePath($path) {
		$this->basePath = $this->savePath($path);
	}

	/**
	 * @return string
	 */
	public function getBasePath() {
		return $this->basePath;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->getDir();
	}
} 