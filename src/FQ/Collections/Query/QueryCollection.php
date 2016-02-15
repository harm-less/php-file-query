<?php

namespace FQ\Collections\Query;

use FQ\Exceptions\QueryCollectionException;
use FQ\Files;
use FQ\Query\FilesQuery;

class QueryCollection {

	/**
	 * @var FilesQuery[]
	 */
	private $_queries;

	function __construct() {
		$this->_queries = array();
	}

	/**
	 * @param $id
	 * @param FilesQuery $query
	 * @return FilesQuery
	 */
	public function addQuery($id, FilesQuery $query) {
		if ($this->queryExists($id)) {
			throw new QueryCollectionException(sprintf('Query with id "%s" already defined', $id));
		}
		$this->_queries[$id] = $query;
		return $query;
	}

	/**
	 * @param string|FilesQuery $query
	 * @return FilesQuery|null
	 */
	public function getQuery($query) {
		if (is_string($query)) {
			return $this->getQueryById($query);
		}
		else if (is_object($query) && $this->isInCollection($query)) {
			return $query;
		}
		return null;
	}

	public function getQueryById($id) {
		if ($this->queryExists($id)) {
			return $this->_queries[$id];
		}
		return null;
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function removeQuery($id) {
		if ($this->queryExists($id)) {
			unset($this->_queries[$id]);
			return true;
		}
		return false;
	}

	/**
	 *
	 */
	public function removeAllQueries() {
		$this->_queries = array();
	}

	/**
	 * @param FilesQuery $query Query that will be checked
	 * @return bool Returns true if dir is in the collection and false when it's not
	 */
	public function isInCollection(FilesQuery $query) {
		foreach ($this->_queries as $queryTemp) {
			if ($query === $queryTemp) return true;
		}
		return false;
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function queryExists($id) {
		return isset($this->_queries[$id]);
	}

	/**
	 * @return FilesQuery[]
	 */
	public function queries() {
		return $this->_queries;
	}

	/**
	 * @return int Total amount of queries in this collection
	 */
	public function totalQueries() {
		return count($this->queries());
	}

}