<?php

namespace FQ\Tests\Dirs;

use FQ\Dirs\RootDir;
use FQ\Tests\AbstractFQTest;

class RootDirTest extends AbstractFQTest {

	public function testCreateNewRootDir()
	{
		$rootDir = $this->_newRootDir();
		$this->assertNotNull($rootDir);
		$this->assertTrue($rootDir instanceof RootDir);
	}
}