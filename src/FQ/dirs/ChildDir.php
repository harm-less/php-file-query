<?php

namespace FQ\Dirs;

use FQ\Files;

class ChildDir extends Dir {

	/**
	 * @var null|string
	 *
	 * @author Harm van der Werf <h.vanderwerf@freetimecompany.nl>
	 */
	private $defaultFileExtension;

	function __construct($relativeDir, $required = false) {
		parent::__construct($relativeDir, $required);
	}

	/**
	 * @param string $extension
	 *
	 * @author Harm van der Werf <h.vanderwerf@freetimecompany.nl>
	 */
	public function setDefaultFileExtension($extension) {
		$this->defaultFileExtension = $extension;
	}

	/**
	 * @return null|string
	 *
	 * @author Harm van der Werf <h.vanderwerf@freetimecompany.nl>
	 */
	public function getDefaultFileExtension() {
		if ($this->defaultFileExtension === null) {
			return Files::DEFAULT_EXTENSION;
		}
		return $this->defaultFileExtension;
	}
} 