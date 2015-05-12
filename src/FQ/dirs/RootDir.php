<?php

namespace FQ\Dirs;

class RootDir extends Dir {

	/**
	 * @var string Directory base path
	 */
	private $_basePath;

	function __construct($id, $absolutePath, $basePath = null, $required = true) {
		parent::__construct($id, $absolutePath, $required);

		$this->basePath($basePath === null ? $absolutePath : $basePath);
	}

	/**
	 * This variable is used in case you want to access a file outside of php.
	 * For example when you want to load assets, echo their path and let the client download the file.
	 *
	 * A possible base path could be http://website.com/assets/rootdir
	 *
	 * @param null $basePath
	 * @return string
	 */
	public function basePath($basePath = null) {
		if (is_string($basePath)) {
			$this->_basePath = $this->parsePath($basePath);
		}
		return $this->_basePath;
	}
}