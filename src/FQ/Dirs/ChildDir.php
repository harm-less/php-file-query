<?php

namespace FQ\Dirs;

use FQ\Files;

class ChildDir extends Dir {

	/**
	 * @var null|string Default file extension when file queries run without an extension
	 */
	private $defaultFileExtension;

	function __construct($id, $relativePathFromRootDirs, $required = false) {
		parent::__construct($id, $relativePathFromRootDirs, $required);
	}

	/**
	 * @param null $extension If this value is not null and a string, it will set it as a new default file extension
	 * @return null|string Return the default file extensions. When nothing has been supplied, it will return the constant from Files::DEFAULT_EXTENSION
	 */
	public function defaultFileExtension($extension = null) {
		if (is_string($extension)) {
			$this->defaultFileExtension = $extension;
		}
		if ($this->defaultFileExtension === null || $this->defaultFileExtension === '') {
			return Files::DEFAULT_EXTENSION;
		}
		return $this->defaultFileExtension;
	}

	/**
	 * @param RootDir $rootDir
	 * @return string
	 */
	public function fullAbsolutePath(RootDir $rootDir) {
		return $rootDir->dir() . '/' . $this->dir();
	}

	/**
	 * @param RootDir $rootDir
	 * @return string
	 */
	public function fullBasePath(RootDir $rootDir) {
		return $rootDir->basePath() . '/' . $this->dir();
	}
} 