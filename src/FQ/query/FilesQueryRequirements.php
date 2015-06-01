<?php

namespace FQ\Query;

use FQ\Exceptions\FileQueryRequirementsException;

class FilesQueryRequirements {

	private $_query;
	/**
	 * @var string[] Requirements of the query
	 */
	private $_requirements;

	/**
	 * @var callable[] Array of callable function that check against certain
	 */
	private $_registeredRequirements;

	/**
	 * Constants determining requirement checking for the query
	 */
	const REQUIRE_ONE = 'require_one';
	const REQUIRE_LAST = 'require_last';
	const REQUIRE_ALL = 'require_all';

	function __construct(FilesQuery $query) {
		$this->_query = $query;
		$this->_requirements = array();

		$this->registerRequirement(self::REQUIRE_ONE, array($this, 'requirementAtLeastOne'));
		$this->registerRequirement(self::REQUIRE_LAST, array($this, 'requirementLast'));
		$this->registerRequirement(self::REQUIRE_ALL, array($this, 'requirementAll'));
	}

	/**
	 * @return null|mixed[]
	 */
	public function requirements() {
		return $this->_requirements;
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
	 * @throws FileQueryRequirementsException Thrown when there provided requirement is neither a string or an integer
	 * @return bool Return true when added. Returns false when it was already part of the requirements
	 */
	public function addRequirement($requirement) {
		if (!(is_string($requirement) || is_int($requirement))) {
			throw new FileQueryRequirementsException(sprintf('A requirement van only be of type integer or string. Provided requirement is of type "%s"', gettype($requirement)));
		}
		if (!$this->requirementIsRegistered($requirement)) {
			throw new FileQueryRequirementsException(sprintf('Trying to add a requirement, but it isn\'t registered. Provided requirement "%s"', $requirement));
		}
		if (!$this->hasRequirement($requirement)) {
			$this->_requirements[] = $requirement;
			return true;
		}
		return false;
	}

	/**
	 * @param string|int $requirement
	 * @return bool Return true when added. Returns false when it was already part of the requirements
	 */
	public function removeRequirement($requirement) {
		if ($this->hasRequirement($requirement)) {
			unset($this->_requirements[array_search($requirement, $this->_requirements)]);
			return true;
		}
		return false;
	}

	/**
	 * Remove all file query requirements
	 */
	public function removeAll() {
		$this->_requirements = array();
	}

	/**
	 * Returns true if the requested requirement is found
	 *
	 * @param string $requirement
	 * @return bool
	 */
	public function hasRequirement($requirement) {
		return in_array($requirement, $this->requirements());
	}

	/**
	 * Check if there are any configured requirements.
	 *
	 * @return bool Returns true if there is at least one requirement, otherwise it will return false
	 */
	public function hasRequirements() {
		return $this->countRequirements() !== 0;
	}

	/**
	 * Count all configured requirements.
	 *
	 * @return int Returns the total number of requirements
	 */
	public function countRequirements() {
		$requirements = $this->requirements();
		return count($requirements);
	}

	/**
	 * @param string $requirement
	 * @return bool Returns true if the requirement is registered. Otherwise it returns false
	 */
	public function requirementIsRegistered($requirement) {
		return array_key_exists($requirement, $this->_registeredRequirements);
	}

	/**
	 * @param string $id
	 * @param callable $callable
	 * @return bool
	 * @throws FileQueryRequirementsException
	 */
	public function registerRequirement($id, $callable) {
		if (is_string($callable) && !method_exists($this, $callable)) {
			throw new FileQueryRequirementsException(sprintf('Trying to register a requirement via a string but the requirement isn\'t callable. Method name "%s"', $callable));
		}
		else if (is_array($callable)) {
			$class = new \ReflectionClass($callable[0]);
			if (!$class->hasMethod($callable[1])) {
				throw new FileQueryRequirementsException(sprintf('Trying to register a requirement but the requirement isn\'t callable. Class name "%s", method name "%s"', get_class($callable[0]), $callable[1]));
			}
		}
		else {
			throw new FileQueryRequirementsException(sprintf('Trying to register a requirement but the requirement\'s callable isn\'t a known data-type. Must be a string or an array. Type given "%s"', gettype($callable)));
		}

		if (isset($this->_registeredRequirements[$id])) {
			return false;
		}
		$this->_registeredRequirements[$id] = $callable;
		return true;
	}

	/**
	 * Count all registered requirements.
	 *
	 * @return int Returns the total number of registered requirements
	 */
	public function countRegisteredRequirements() {
		return count($this->_registeredRequirements);
	}

	/**
	 * Check whether a requirements has already been registered
	 * @param string $id ID of the requirement
	 * @return bool Return true if the requirement is already registered
	 */
	public function isRegisteredRequirement($id) {
		return isset($this->_registeredRequirements[$id]);
	}

	/**
	 * @param string $id
	 * @param FilesQuery $query
	 * @throws FileQueryRequirementsException
	 * @return mixed
	 */
	public function tryRequirement($id, FilesQuery $query) {
		if (!$this->requirementIsRegistered($id)) {
			throw new FileQueryRequirementsException(sprintf('Trying to call a requirement, but it isn\'t registered. Provided requirement "%s"', $id), 10);
		}
		return call_user_func($this->_registeredRequirements[$id], $query);
	}

	/**
	 * @param FilesQuery $query
	 * @return bool|FileQueryRequirementsException
	 */
	protected function requirementAtLeastOne(FilesQuery $query) {
		if (!$query->hasPaths() >= 1) {
			return new FileQueryRequirementsException(sprintf('At least 1 file must be available for file "%s". Please create the file in any of these locations: "%s"', $query->queriedFileName(), implode('", "', $query->listRawPathsSimple())));
		}
		return true;
	}

	/**
	 * @param FilesQuery $query
	 * @return bool|FileQueryRequirementsException
	 */
	protected function requirementLast(FilesQuery $query) {
		$oneExists = false;

		$lastRootDirId = null;
		$rootSelection = $query->getCurrentRootDirSelection();
		if (count($rootSelection) >= 1) {
			$lastRootDirId = $rootSelection[count($rootSelection) - 1]->id();

			foreach ($query->queryChildDirs(true) as $child) {
				$pathExist = $child->pathsExist();
				if ($pathExist[$lastRootDirId] !== false) {
					$oneExists = true;
					break;
				}
			}
		}

		if (!$oneExists) {
			if ($lastRootDirId === null) {
				return new FileQueryRequirementsException('Query requires at least one file to exist in at least one child directory, but there isn\'t even a root directory. Make sure you have at least one root directory in your query');
			}

			$paths = array();
			foreach ($query->queryChildDirs(true) as $child) {
				$rawPaths = $child->rawAbsolutePaths();
				$paths = array_merge($paths, (array) $rawPaths[$lastRootDirId]);
			}
			return new FileQueryRequirementsException(sprintf('Query requires at least one file to exist in at least one child directory in the last root directory with ID "%s". File must be present in one of the following locations: "%s"', $lastRootDirId, implode('", "', $paths)));
		}
		return true;
	}

	/**
	 * @param FilesQuery $query
	 * @return bool|FileQueryRequirementsException
	 */
	protected function requirementAll(FilesQuery $query) {
		if (count($query->listPathsSimple()) != count($query->listRawPathsSimple())) {
			$totalNeeded = count($query->listRawPathsSimple());
			$totalAvailable = count($query->listPathsSimple());

			$neededPaths = array_diff($query->listRawPathsSimple(), $query->listPathsSimple());
			return new FileQueryRequirementsException(sprintf('All root directories and children must contain a file called "%s". Total files needed is %s but only %s files exist. Make sure that file is also available in the following locations: "%s"', $query->queriedFileName(), $totalNeeded, $totalAvailable, implode('", "', $neededPaths)));
		}
		return true;
	}

	/**
	 * Checks if the query meets all its requirements
	 *
	 * @param bool $throwExceptionOnFail
	 * @throws \Exception When $throwExceptionOnFail is set to true and one of the requirements fails, it will throw
	 * the exception from that fail. Otherwise this exception will be returned
	 * @return mixed Returns true if all requirements are met. Otherwise returns an un-thrown exception
	 * if 'throwExceptionOnFail' is set to false or the response from the requirement
	 */
	public function meetsRequirements($throwExceptionOnFail = true) {
		// if there are no requirements it certainly is valid and it can be returned immediately
		if (!$this->hasRequirements()) {
			return true;
		}

		foreach ($this->requirements() as $requirement) {
			$attempt = $this->tryRequirement($requirement, $this->_query);
			if ($attempt instanceof \Exception && $throwExceptionOnFail === true) {
				throw $attempt;
			}
			else if ($attempt !== true ) {
				return $attempt;
			}
		}
		return true;
	}
}