<?php

namespace FQ\Tests\Dirs;

use FQ\Dirs\ChildDir;
use FQ\Files;
use FQ\Tests\AbstractFQTest;

class ChildDirTest extends AbstractFQTest {

	public function testCreateNewRootDir()
	{
		$childDir = new ChildDir(self::CHILD_DIR_DEFAULT);
		$this->assertNotNull($childDir);
		$this->assertTrue($childDir instanceof ChildDir);
	}

	public function testDefaultFileExtension() {
		$childDir = $this->_newActualChildDir();
		$this->assertEquals(Files::DEFAULT_EXTENSION, $childDir->defaultFileExtension());
	}
	public function testCustomFileExtensionAndResetItAfterwards() {
		$childDir = $this->_newActualChildDir();
		$childDir->defaultFileExtension('.xml');
		$this->assertEquals('.xml', $childDir->defaultFileExtension());

		$childDir->defaultFileExtension('');
		$this->assertEquals(Files::DEFAULT_EXTENSION, $childDir->defaultFileExtension());
	}
}