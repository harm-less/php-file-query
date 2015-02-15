<?php

namespace FQ\Dirs;

use FQ\Files;

class ChildDir extends Dir {

	/**
	 * @var null|string Default file extension when file queries run without an extension
	 */
	private $defaultFileExtension;

	function __construct($relativePathFromRootDirs, $required = false) {
		parent::__construct($relativePathFromRootDirs, $required);
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
} 