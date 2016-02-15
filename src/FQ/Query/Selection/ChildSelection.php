<?php

namespace FQ\Query\Selection;

class ChildSelection extends DirSelection {

	function __construct() {
		parent::__construct();
	}

	/**
	 * @return ChildSelection
	 */
	public function copy() {
		return parent::copy();
	}

	/**
	 * @return ChildSelection
	 */
	protected function _createInstance() {
		return new ChildSelection();
	}
}