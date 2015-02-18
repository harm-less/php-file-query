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
	 * @param mixed[] $items
	 */
	public function addItems($items) {
		$items = (array) $items;
		foreach ($items as $item) {
			$this->addItem($item);
		}
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