<?php

namespace FQ\Tests\Query;

use FQ\Collections\Dirs\DirCollection;
use FQ\Query\FilesQuery;
use FQ\Query\FilesQueryChild;
use FQ\Tests\AbstractFQTest;

class AbstractFilesQueryTests extends AbstractFQTest {

	/**
	 * @var FilesQuery
	 */
	protected $_query;

	/**
	 * @var FilesQueryChild
	 */
	protected $_queryChild;

	protected function setUp()
	{
		parent::setUp();

		// Create a new query app,
		$this->_query = $this->_fqApp->query();
		$this->_queryChild = $this->_newActualQueryChild();
	}

	/**
	 * @return FilesQuery
	 */
	protected function query() {
		return $this->_query;
	}
	/**
	 * @return FilesQueryChild
	 */
	protected function queryChild() {
		return $this->_queryChild;
	}

	protected function runQuery($filename = 'File2') {
		return $this->query()->run($filename);
	}

	/**
	 * @return FilesQuery
	 */
	protected function _createNewFilesQuery() {
		return new FilesQuery($this->_fqApp);
	}

	protected function _newActualQueryChild() {
		$queryChild = new FilesQueryChild($this->_fqApp->query(), $this->_newActualChildDir());
		$queryChild->setRootDirs(array($this->_newActualRootDir()));
		return $queryChild;
	}
}