<?php

namespace FQ\Tests;

use FQ\Dirs\RootDir;

class RootDirTest extends AbstractFQTest {

	public function testCreateNewRootDir()
	{
		$rootDir = $this->_newRootDir();
		$this->assertNotNull($rootDir);
		$this->assertTrue($rootDir instanceof RootDir);
	}
}