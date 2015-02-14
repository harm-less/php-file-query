<?php

namespace FQ\Tests\Dirs;

use FQ\Dirs\Dir;
use FQ\Tests\AbstractFQTest;

class DirTest extends AbstractFQTest {

	const FICTITIOUS_DIR_NAME = 'fictitious_dir_name';
	const ACTUAL_DIR_NAME = __DIR__;

	protected function _createDir($dir = self::ACTUAL_DIR_NAME, $required = false) {
		return new Dir($dir, $required);
	}

	public function testConstructor()
	{
		$dir = $this->_createDir();
		$this->assertNotNull($dir);
		$this->assertTrue($dir instanceof Dir);
	}

	public function testIdIfNoIdHasBeenSuppliedManually()
	{
		$dir = $this->_createDir();
		$this->assertEquals(self::ACTUAL_DIR_NAME, $dir->id());
	}
}