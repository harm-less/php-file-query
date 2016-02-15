<?php

namespace FQ\Query\Selection;

class RootSelection extends DirSelection {

	function __construct() {
		parent::__construct();
	}

	/**
	 * @return RootSelection
	 */
	public function copy() {
		return parent::copy();
	}

	/**
	 * @return RootSelection
	 */
	protected function _createInstance() {
		return new RootSelection();
	}
}