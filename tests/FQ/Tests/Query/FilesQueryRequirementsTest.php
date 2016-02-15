<?php

namespace FQ\Tests\Query;

use FQ\Core\CallableCollection;

class FilesQueryRequirementsTest extends AbstractFilesQueryTests {

	/**
	 * @var CallableCollection
	 */
	private $_collection;

	const CALLABLE_NORMAL = 'callable';
	const CALLABLE_WITH_PARAMETER = 'callable';

	protected function setUp()
	{
		parent::setUp();

		$this->_collection = new CallableCollection();
	}

	/**
	 * @return CallableCollection
	 */
	protected function _collection() {
		return $this->_collection;
	}

	protected function _registerCallable($id = self::CALLABLE_NORMAL) {
		return $this->_collection()->registerCallable($id, array($this, 'callableMethod'));
	}
	protected function _registerCallableWithParameters($id = self::CALLABLE_WITH_PARAMETER) {
		return $this->_collection()->registerCallable($id, array($this, 'callableMethodWithParameter'));
	}

	public function callableMethod() {
		return 'Hello world';
	}
	public function callableMethodWithParameter($name) {
		return 'Hello ' . $name;
	}
	private function privateCallableMethod() {
		return 'No hello world';
	}

	public function testConstructor() {
		$callableCollection = new CallableCollection();
		$this->assertNotNull($callableCollection);
		$this->assertTrue($callableCollection instanceof CallableCollection);
	}
}