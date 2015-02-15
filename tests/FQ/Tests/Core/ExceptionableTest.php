<?php

namespace FQ\Tests\Dirs;

use FQ\Core\Exceptionable;
use FQ\Tests\AbstractFQTest;

class ExceptionableTest extends AbstractFQTest {

	protected function setUp()
	{
		parent::setUp();
		$this->nonPublicMethodObject(new Exceptionable());
	}

	public function testCreateNewExceptionable() {
		$exceptionable = new Exceptionable();
		$this->assertNotNull($exceptionable);
		$this->assertTrue($exceptionable instanceof Exceptionable);
	}

	public function testThrowError() {
		$exceptionable = new Exceptionable();
		$this->assertFalse($exceptionable->throwErrors());

		$exceptionable->throwErrors('True');
		$this->assertFalse($exceptionable->throwErrors());

		$exceptionable->throwErrors(true);
		$this->assertTrue($exceptionable->throwErrors());

		$exceptionable->throwErrors(false);
		$this->assertFalse($exceptionable->throwErrors());
	}

	public function testThrowExceptionWithThrowErrorsSetTotFalse() {
		$result = $this->callNonPublicMethod('_throwError', 'Error message');
		$this->assertFalse($result);
	}
	public function testThrowExceptionWithThrowErrorsSetTotTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');
		$instance = $this->nonPublicMethodObject();
		$instance->throwErrors(true);
		$result = $this->callNonPublicMethod('_throwError', 'Error message');
	}

	public function testThrowExceptionWithThrowErrorsSetTotTrueButItWillBeIgnored() {
		$instance = $this->nonPublicMethodObject();
		$instance->throwErrors(true);
		$result = $this->callNonPublicMethod('_throwError', array('Error message', true));
		$this->assertFalse($result);
	}
}