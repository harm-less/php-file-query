<?php

namespace FQ\Dirs;

class Dir {

	/**
	 * @var string Unique
	 */
	private $id;

	/**
	 * @var string
	 */
	private $dir;

	/**
	 * @var bool
	 */
	private $required;

	function __construct($dir, $required = false) {
		$this->setDir($dir);
		$this->isRequired($required);
	}

	/**
	 * @param string $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return string Uses dir if the id variable is null
	 */
	public function getId() {
		if ($this->id === null) {
			return $this->getDir();
		}
		return $this->id;
	}

	/**
	 * @param string $dir
	 */
	public function setDir($dir) {
		$this->dir = $this->savePath($dir);
	}

	/**
	 * @return string
	 */
	public function getDir() {
		return $this->dir;
	}

	/**
	 * @param null|bool $required
	 * @return bool
	 */
	public function isRequired($required = null) {
		if ($required !== null && is_bool($required)) {
			$this->required = $required;
		}
		return $this->required;
	}

	/**
	 * @param string $path
	 * @return string Returns a save path by removing a potential trailing slash
	 */
	protected function savePath($path) {
		return rtrim($path, '/\\');
	}

} 