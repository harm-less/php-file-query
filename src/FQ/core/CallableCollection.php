<?php

namespace FQ\Core;

use FQ\Exceptions\CallableCollectionException;

class CallableCollection extends Collection {

	/**
	 * @var callable[] Array of callable functions
	 */
	private $_callableMethods;

	function __construct() {
		parent::__construct();

		$this->_callableMethods = array();
	}

	public function callableMethods() {
		return $this->_callableMethods;
	}

	/**
	 * @param string $id
	 * @param callable $callable
	 * @throws CallableCollectionException
	 * @return bool
	 */
	public function registerCallable($id, $callable) {
		if (!is_array($callable) || count($callable) !== 2 || !is_object($callable[0]) || !is_string($callable[1])) {
			throw new CallableCollectionException('Trying to register a callable item but the callable parameter isn\'t set up properly. Expected format "array(classInstance, \'methodName\')"');
		}
		if (!method_exists(get_class($callable[0]), $callable[1])) {
			throw new CallableCollectionException(sprintf('Trying to register a callable item but the method doesn\'t seem to exist. Trying method "%s" from class "%s"', $callable[1], get_class($callable[0])));
		}
		if (!is_callable($callable)) {
			throw new CallableCollectionException(sprintf('Trying to register a callable item but the value doesn\'t seem callable. Trying method "%s" from class "%s"', $callable[1], get_class($callable[0])));
		}
		$this->_callableMethods[$id] = $callable;
		return true;
	}

	/**
	 * @param $callableId
	 * @return bool Returns true if the callable is registered. Otherwise it returns false
	 */
	public function callableIsRegistered($callableId) {
		return array_key_exists($callableId, $this->callableMethods());
	}

	/**
	 * @param string $id
	 * @param $parameters
	 * @throws CallableCollectionException
	 * @return mixed
	 */
	public function tryCallable($id, $parameters = null) {
		if (!$this->callableIsRegistered($id)) {
			throw new CallableCollectionException(sprintf('Trying to call a callable, but it isn\'t registered. Provided callable id "%s"', $id), 10);
		}
		return call_user_func_array($this->_callableMethods[$id], (array) $parameters);
	}
}