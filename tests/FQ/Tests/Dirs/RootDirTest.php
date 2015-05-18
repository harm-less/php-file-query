<?php

namespace FQ\Tests\Dirs;

use FQ\Dirs\RootDir;
use FQ\Tests\AbstractFQTest;

class RootDirTest extends AbstractFQTest {

	public function testCreateNewRootDir()
	{
		$rootDir = new RootDir(self::ROOT_DIR_DEFAULT_ID, self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH);
		$this->assertNotNull($rootDir);
		$this->assertTrue($rootDir instanceof RootDir);
	}

	public function testBasePathConstructor() {
		$rootDir = $this->_newActualRootDir();
		$this->assertEquals(self::ROOT_DIR_DEFAULT_BASE_PATH, $rootDir->basePath());
	}

	public function testBasePathDefault() {
		$rootDir = $this->_newActualRootDir();
		$this->assertEquals(self::ROOT_DIR_DEFAULT_BASE_PATH, $rootDir->basePath());
	}

	public function testBasePathCustom() {
		$rootDir = $this->_newActualRootDir();
		$rootDir->basePath('http://basedir.com/root');
		$this->assertEquals('http://basedir.com/root', $rootDir->basePath());
	}
}