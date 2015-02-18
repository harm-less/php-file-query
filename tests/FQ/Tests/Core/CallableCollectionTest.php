<?php

namespace FQ\Tests\Core;

use FQ\Core\CallableCollection;
use FQ\Tests\AbstractFQTest;

class CallableCollectionTest extends AbstractFQTest {

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

	public function testCallableMethods() {
		$this->assertTrue(is_array($this->_collection()->callableMethods()));
		$this->assertEquals(0, count($this->_collection()->callableMethods()));
	}

	public function testRegisterCallable() {
		$this->assertTrue($this->_registerCallable());
	}
	public function testRegisterCallableWithFaultyCallable() {
		$this->setExpectedException('FQ\Exceptions\CallableCollectionException', 'Trying to register a callable item but the callable parameter isn\'t set up properly. Expected format "array(classInstance, \'methodName\')"');
		$this->_collection()->registerCallable('test', 'callableMethod');
	}
	public function testRegisterCallableWithNonExistingCallable() {
		$this->setExpectedException('FQ\Exceptions\CallableCollectionException', 'Trying to register a callable item but the method doesn\'t seem to exist. Trying method "nonCallableMethod" from class "FQ\Tests\Core\CallableCollectionTest"');
		$this->_collection()->registerCallable('test', array($this, 'nonCallableMethod'));
	}
	public function testRegisterCallableWithNonProtectedCallable() {
		$this->setExpectedException('FQ\Exceptions\CallableCollectionException', 'Trying to register a callable item but the value doesn\'t seem callable. Trying method "privateCallableMethod" from class "FQ\Tests\Core\CallableCollectionTest"');
		$this->_collection()->registerCallable('test', array($this, 'privateCallableMethod'));
	}

	public function testCallableIsRegistered() {
		$this->_collection()->registerCallable('exists', array($this, 'callableMethod'));
		$this->assertTrue($this->_collection()->callableIsRegistered('exists'));
	}
	public function testCallableIsNotRegistered() {
		$this->assertFalse($this->_collection()->callableIsRegistered('non-existent'));
	}

	public function testTryCallable() {
		$this->_registerCallable();
		$this->assertEquals('Hello world', $this->_collection()->tryCallable(self::CALLABLE_NORMAL));
	}
	public function testTryNonExistentCallable() {
		$this->setExpectedException('FQ\Exceptions\CallableCollectionException', 'Trying to call a callable, but it isn\'t registered. Provided callable id "callable"');
		$this->_collection()->tryCallable(self::CALLABLE_NORMAL);
	}
	public function testTryCallableWithParameter() {
		$this->_registerCallableWithParameters();
		$this->assertEquals('Hello advanced world', $this->_collection()->tryCallable(self::CALLABLE_NORMAL, array('advanced world')));
	}
}