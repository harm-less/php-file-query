<?php

namespace FQ\Tests\Collections\Query;

use FQ\Collections\Query\QueryCollection;
use FQ\Query\FilesQuery;

class DirCollectionTest extends AbstractQueryCollectionTests {

	public function testCreateNewQueryCollection() {
		$queryCollection = new QueryCollection($this->_fqApp);
		$this->assertNotNull($queryCollection);
		$this->assertTrue($queryCollection instanceof QueryCollection);
	}

	public function testAddQuery() {
		$query = $this->_addQueryToCollection();
		$this->assertNotNull($query);
		$this->assertEquals(1, $this->queryCollection()->totalQueries());
	}
	public function testAddQueryWithSameId() {
		$this->setExpectedException('\FQ\Exceptions\QueryCollectionException');
		$this->_addQueryToCollection();
		$this->_addQueryToCollection();
	}

	public function testAddMultipleQueries() {
		$firstQuery = $this->_addQueryToCollection();
		$secondQuery = $this->_addQueryToCollection(self::SECOND_QUERY_ID);
		$this->assertNotNull($firstQuery);
		$this->assertNotNull($secondQuery);
		$this->assertEquals(2, $this->queryCollection()->totalQueries());
	}

	public function testRemoveQuery() {
		$query = $this->_addQueryToCollection();
		$this->_addQueryToCollection('custom-id');
		$collection = $this->queryCollection();
		$this->assertTrue($collection->removeQuery('custom-id'));
		$this->assertEquals(array(
			self::DEFAULT_QUERY_ID => $query
		), $collection->queries());
	}
	public function testRemoveQueryNotPresentInTheCollection() {
		$collection = $this->queryCollection();
		$this->assertFalse($collection->removeQuery('does-not-exist'));
	}
	public function testRemoveAllDirs() {
		$this->_addQueryToCollection();
		$this->_addQueryToCollection(self::SECOND_QUERY_ID);
		$collection = $this->queryCollection();
		$collection->removeAllQueries();
		$this->assertEquals(array(), $collection->queries());
	}
	public function testGetDirByIdThatDoesNotExist() {
		$this->assertNull($this->queryCollection()->getQueryById('id_that_does_not_exist'));
	}

	public function testGetDir() {
		$dir = $this->_addQueryToCollection();
		$this->assertEquals($dir, $this->queryCollection()->getQuery($dir));
		$this->assertEquals($dir, $this->queryCollection()->getQuery(self::DEFAULT_QUERY_ID));
		$this->assertNull($this->queryCollection()->getQuery(null));
	}

	public function testIsInCollection() {
		$query = $this->_addQueryToCollection();
		$this->assertTrue($this->queryCollection()->isInCollection($query));
		$this->assertFalse($this->queryCollection()->isInCollection(new FilesQuery($this->_fqApp)));
	}
}