<?php

namespace FQ\Tests\Samples;

class SimpleSampleTest extends AbstractSampleTest {

	function __construct() {
		parent::__construct('\\FQ\\Samples\\Simple');
	}

	public function testF() {
		$this->assertNull(null);
	}
}