<?php

namespace FQ\Query;

use FQ\Core\CallableCollection;
use FQ\Exceptions\CallableCollectionException;
use FQ\Exceptions\FileQueryException;
use FQ\Exceptions\FileQueryRequirementsException;

class FilesQueryRequirements extends CallableCollection {

	/**
	 * Constants determining requirement checking for the query
	 */
	const LEVELS_ONE = 'levels_one';
	const LEVELS_LAST = 'levels_last';
	const LEVELS_ALL = 'levels_all';

	function __construct() {
		parent::__construct();
		$this->registerRequirement(self::LEVELS_ONE, array($this, 'requirementAtLeastOne'));
		$this->registerRequirement(self::LEVELS_LAST, array($this, 'requirementLast'));
		$this->registerRequirement(self::LEVELS_ALL, array($this, 'requirementAll'));
	}

	/**
	 * @return null|mixed[]
	 */
	public function requirements() {
		return $this->collection();
	}

	/**
	 * @param int|string|string[] $requirements
	 */
	public function addRequirements($requirements) {
		$requirements = (array) $requirements;
		foreach ($requirements as $requirement) {
			$this->addRequirement($requirement);
		}
	}

	/**
	 * @param string|int $requirement
	 * @throws FileQueryException Thrown when there provided requirement is neither a string or an integer
	 * @return bool Return true when added. Returns false when it was already part of the requirements
	 */
	public function addRequirement($requirement) {
		if (!$this->requirementIsRegistered($requirement)) {
			throw new FileQueryException(sprintf('Trying to add a requirement, but it isn\'t registered. Provided requirement "%s"', $requirement));
		}
		return $this->addItem($requirement);
	}

	/**
	 * @param string|int $requirement
	 * @return bool Return true when added. Returns false when it was already part of the requirements
	 */
	public function removeRequirement($requirement) {
		return $this->removeItem($requirement);
	}

	/**
	 * Returns true if the requested requirement is found
	 *
	 * @param string $requirement
	 * @return bool
	 */
	public function hasRequirement($requirement) {
		return $this->hasItem($requirement);
	}

	/**
	 * Check if there are any configured requirements.
	 *
	 * @return bool Returns true if there is at least one requirement, otherwise it will return false
	 */
	public function hasRequirements() {
		return $this->hasItems();
	}

	/**
	 * @param string $requirement
	 * @return bool Returns true if the requirement is registered. Otherwise it returns false
	 */
	public function requirementIsRegistered($requirement) {
		return $this->callableIsRegistered($requirement);
	}

	/**
	 * @param string $id
	 * @param callable $callable
	 * @return bool
	 * @throws CallableCollectionException
	 */
	public function registerRequirement($id, $callable) {
		return $this->registerCallable($id, $callable);
	}

	/**
	 * @param string $id
	 * @param FilesQueryChild $child
	 * @throws CallableCollectionException
	 * @return mixed
	 */
	public function tryRequirement($id, FilesQueryChild $child) {
		return $this->tryCallable($id, $child);
	}

	/**
	 * Checks if the query meets all its requirements
	 *
	 * @param FilesQueryChild $queryChild
	 * @param bool $throwExceptionOnFail
	 * @throws \Exception
	 * @return mixed Returns true if all requirements are met. Otherwise returns an un-thrown exception if 'throwExceptionOnFail' is set to false or the response from the requirement
	 */
	public function meetsRequirements(FilesQueryChild $queryChild, $throwExceptionOnFail = true) {
		// if there are no requirements it certainly is valid and it can be returned immediately
		if (!$this->hasRequirements()) {
			return true;
		}

		foreach ($this->requirements() as $requirement) {
			$attempt = $this->tryRequirement($requirement, $queryChild);
			if ($attempt instanceof \Exception && $throwExceptionOnFail === true) {
				throw $attempt;
			}
			else if ($attempt !== true ) {
				return $attempt;
			}
		}
		return true;
	}

	/**
	 * @param FilesQueryChild $child
	 * @return bool|FileQueryException
	 */
	protected function requirementAtLeastOne(FilesQueryChild $child) {
		if ($child->totalExistingPaths() === 0) {
			return new FileQueryRequirementsException(sprintf('At least 1 file must be available for file "%s" in child with an id of "%s". Please create the file in any of these locations: %s', $child->relativePath(), $child->childDir()->id(), implode($child->rawAbsolutePaths())));
		}
		return true;
	}

	/**
	 * @param FilesQueryChild $child
	 * @return bool|FileQueryException
	 */
	protected function requirementLast(FilesQueryChild $child) {
		$pathsExist = $child->pathsExist();
		if ($child->totalExistingPaths() === 0 || $pathsExist[0] == null) {
			return new FileQueryException(sprintf('Last file "%s" not found in child "%s" but it is required', $child->relativePath(), $child->childDir()->id()));
		}
		return true;
	}

	/**
	 * @param FilesQueryChild $child
	 * @return bool|FileQueryException
	 */
	protected function requirementAll(FilesQueryChild $child) {
		if ($child->totalExistingPaths() != $child->files()->totalRootDirs()) {
			return new FileQueryException(sprintf('All "%s" children must contain a file called "%s".', $child->childDir()->id(), $child->relativePath()));
		}
		return true;
	}
}