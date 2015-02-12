<?php

namespace FQ\Core;

use FQ\Exceptions\ExceptionableException;

class Exceptionable {

	/**
	 * @var bool
	 */
	private $_throwErrors = false;

	function __construct() {
	}

	/**
	 * @param $bool null|bool Provide a bool to change this variable
	 * @return bool Returns a boolean to indicate if errors should be thrown or or not
	 */
	public function throwErrors($bool = null) {
		if (is_bool($bool)) {
			$this->_throwErrors = $bool;
		}
		return $this->_throwErrors;
	}

	/**
	 * @param $error
	 * @param $ignoreThrowError
	 * @return bool When throwErrors() is set to false, it will return false
	 * @throws ExceptionableException When throwErrors() is set to true, this will result in the provided error to be thrown
	 */
	protected function _throwError($error, $ignoreThrowError = false) {
		if ($ignoreThrowError === false && $this->throwErrors()) {
			throw new ExceptionableException($error);
		}
		else {
			return false;
		}
	}
}