<?php

namespace FQ\Tests\Core;

use FQ\Core\Collection;
use FQ\Tests\AbstractFQTest;

class CollectionTest extends AbstractFQTest {

	/**
	 * @var Collection
	 */
	private $_collection;

	protected function setUp()
	{
		parent::setUp();

		$this->_collection = new Collection();
	}

	/**
	 * @return Collection
	 */
	protected function collection() {
		return $this->_collection;
	}

	public function testConstructor() {
		$dir = new Collection();
		$this->assertNotNull($dir);
		$this->assertTrue($dir instanceof Collection);
	}

	public function testGetCollectionWithOneItem() {
		$this->collection()->addItem('item');
		$this->assertEquals(1, $this->collection()->count());
	}

	public function testAddItem() {
		$this->assertTrue($this->collection()->addItem('item'));
		$this->assertEquals(1, $this->collection()->count());
	}

	public function testAddTwoItems() {
		$this->collection()->addItem('item1');
		$this->collection()->addItem('item2');
		$this->assertEquals(2, $this->collection()->count());
	}

	public function testAddTwoItemsThatAreTheSame() {
		$this->assertTrue($this->collection()->addItem('item'));
		$this->assertFalse($this->collection()->addItem('item'));
		$this->assertEquals(1, $this->collection()->count());
	}

	public function testRemoveItem() {
		$this->collection()->addItem('item');
		$this->collection()->removeItem('item');
		$this->assertEquals(0, $this->collection()->count());
	}

	public function testRemoveItemThatDoesNotExist() {
		$this->collection()->removeItem('item');
		$this->assertEquals(0, $this->collection()->count());
	}

	public function testRemoveAll() {
		$this->collection()->addItem('item1');
		$this->collection()->addItem('item2');
		$this->collection()->removeAll();
		$this->assertEquals(0, $this->collection()->count());
	}

	public function testHasItem() {
		$this->collection()->addItem('item');
		$this->assertTrue($this->collection()->hasItem('item'));
		$this->assertFalse($this->collection()->hasItem('item_that_does_not_exist'));
	}

	public function testHasItems() {
		$this->assertFalse($this->collection()->hasItems());
		$this->collection()->addItem('item');
		$this->assertTrue($this->collection()->hasItems());
	}

	public function testCount() {
		$this->assertEquals(0, $this->collection()->count());
		$this->collection()->addItem('item');
		$this->assertEquals(1, $this->collection()->count());
	}
}