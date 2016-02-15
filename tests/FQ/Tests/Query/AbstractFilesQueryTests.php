<?php

namespace FQ\Tests\Query;

use FQ\Collections\Dirs\DirCollection;
use FQ\Query\FilesQuery;
use FQ\Tests\AbstractFQTest;

class AbstractFilesQueryTests extends AbstractFQTest {

	protected $_query;

	protected function setUp()
	{
		parent::setUp();

		// Create a new query app,
		$this->_query = $this->_createNewFilesQuery();
	}

	/**
	 * @return FilesQuery
	 */
	protected function query() {
		return $this->_query;
	}

	/**
	 * @return FilesQuery
	 */
	protected function _createNewFilesQuery() {
		return new FilesQuery($this->_fqApp);
	}
}