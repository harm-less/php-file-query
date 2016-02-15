<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQuery;
use FQ\Tests\AbstractFQTest;

class FilesQueryTest extends AbstractFQTest {

	public function testConstructor()
	{
		$files = new FilesQuery($this->_fqApp);
		$this->assertNotNull($files);
		$this->assertTrue($files instanceof FilesQuery);
	}

}