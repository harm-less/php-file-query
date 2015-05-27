<?php

namespace FQ\Tests\Collections\Query;

use FQ\Collections\Query\QueryCollection;
use FQ\Query\FilesQuery;
use FQ\Tests\AbstractFQTest;

class AbstractQueryCollectionTests extends AbstractFQTest
{
	protected $_queryCollection;

	const DEFAULT_QUERY_ID = 'query1';
	const SECOND_QUERY_ID = 'query2';

	protected function setUp()
	{
		parent::setUp();
		// Create a new FQ app,
		// since we need one pretty much everywhere
		$this->_queryCollection = $this->_createNewQueryCollection();
	}

	/**
	 * @return QueryCollection
	 */
	protected function queryCollection()
	{
		return $this->_queryCollection;
	}

	/**
	 * @return QueryCollection
	 */
	protected function _createNewQueryCollection()
	{
		return new QueryCollection($this->_fqApp);
	}

	protected function _addQueryToCollection($id = self::DEFAULT_QUERY_ID, FilesQuery $query = null)
	{
		$query = $query === null ? new FilesQuery($this->_fqApp) : $query;
		$collection = $this->queryCollection();
		return $collection->addQuery($id, $query);
	}

	function testIgnore() {

	}
}