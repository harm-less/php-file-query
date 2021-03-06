<?php

namespace FQ\Tests\Dirs;

use FQ\Dirs\ChildDir;
use FQ\Files;
use FQ\Tests\AbstractFQTest;

class ChildDirTest extends AbstractFQTest {

	public function testCreateNewRootDir()
	{
		$childDir = new ChildDir(self::CHILD_DIR_DEFAULT_DIR, self::CHILD_DIR_DEFAULT_DIR);
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

	public function testFullAbsolutePath() {
		$rootDir = $this->_newActualRootDir();
		$childDir = $this->_newActualChildDir();
		$this->assertEquals(self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/' . self::CHILD_DIR_DEFAULT_DIR, $childDir->fullAbsolutePath($rootDir));
	}

	public function testFullBasePath() {
		$rootDir = $this->_newActualRootDir();
		$childDir = $this->_newActualChildDir();
		$this->assertEquals(self::ROOT_DIR_DEFAULT_BASE_PATH . '/' . self::CHILD_DIR_DEFAULT_DIR, $childDir->fullBasePath($rootDir));
	}
}