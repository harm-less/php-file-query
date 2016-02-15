<?php

namespace FQ\Core;

class Collection {

	/**
	 * @var mixed[] Collection
	 */
	private $_collection;


	function __construct() {
		$this->_collection = array();
	}

	/**
	 * @return mixed[]
	 */
	public function collection() {
		return $this->_collection;
	}

	/**
	 * @param mixed $item
	 * @return bool Return true when added. Returns false when it was already part of the collection
	 */
	public function addItem($item) {
		if (!$this->hasItem($item)) {
			$this->_collection[] = $item;
			return true;
		}
		return false;
	}

	/**
	 * @param string $id
	 * @param mixed $item
	 * @return bool Return true when added. Returns false when it was already part of the collection either by id or item
	 */
	public function addAssociatedItem($id, $item) {
		if (!$this->hasItem($item) && !isset($this->_collection[$id])) {
			$this->_collection[$id] = $item;
			return true;
		}
		return false;
	}

	/**
	 * @param string $id
	 * @return mixed Returns the value with it was registered. Returns false when it wasn't found
	 */
	public function getItemById($id) {
		if (isset($this->_collection[$id])) {
			return $this->_collection[$id];
		}
		return false;
	}

	/**
	 * @param mixed $item
	 * @return bool Return true when added. Returns false when it was already part of the requirements
	 */
	public function removeItem($item) {
		if ($this->hasItem($item)) {
			unset($this->_collection[array_search($item, $this->_collection)]);
			return true;
		}
		return false;
	}

	/**
	 * Remove all items from the collection
	 */
	public function removeAll() {
		$this->_collection = array();
	}

	/**
	 * Returns true if the requested item is found
	 *
	 * @param string $item
	 * @return bool
	 */
	public function hasItem($item) {
		return in_array($item, $this->collection());
	}

	/**
	 * Check if there are any configured requirements.
	 *
	 * @return bool Returns true if there is at least one requirement, otherwise it will return false
	 */
	public function hasItems() {
		return $this->count() !== 0;
	}

	public function count() {
		return count($this->collection());
	}
}