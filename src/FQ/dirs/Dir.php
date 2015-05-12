<?php

namespace FQ\Dirs;

class Dir {

	/**
	 * @var string Unique
	 */
	private $_id;

	/**
	 * @var string
	 */
	private $_dir;

	/**
	 * @var bool
	 */
	private $_required;

	function __construct($id, $dir, $required = false) {
		$this->id($id);
		$this->dir($dir);
		$this->isRequired($required);
	}

	/**
	 * @param string $id
	 * @return string Returns the ID of the Dir object. If no ID is set, it returns the "dir" variable
	 */
	public function id($id = null) {
		if (is_string($id)) {
			$this->_id = $id;
		}

		if ($this->_id === null || $this->_id === '') {
			return $this->dir();
		}
		return $this->_id;
	}

	/**
	 * @param string|null $dir
	 * @return string
	 */
	public function dir($dir = null) {
		if (is_string($dir)) {
			$this->_dir = $this->parsePath($dir);
		}
		return $this->_dir;
	}

	/**
	 * @param null|bool $required
	 * @return bool
	 */
	public function isRequired($required = null) {
		if (is_bool($required)) {
			$this->_required = $required;
		}
		return $this->_required;
	}

	/**
	 * @param string $path
	 * @return string Returns a save path by removing a potential trailing slashes
	 */
	protected function parsePath($path) {
		return rtrim($path, '/\\');
	}

} 