<?php

namespace FQ\Tests\Dirs;

use FQ\Dirs\Dir;
use FQ\Tests\AbstractFQTest;

class DirTest extends AbstractFQTest {

	const FICTITIOUS_DIR_NAME = 'fictitious_dir_name';
	const ACTUAL_DIR_NAME = __DIR__;

	protected function setUp()
	{
		parent::setUp();
		$this->nonPublicMethodObject($this->_createActualDir());
	}

	protected function __createDir($dir = self::ACTUAL_DIR_NAME, $required = false) {
		return new Dir($dir, $required);
	}
	protected function _createActualDir($required = false) {
		return $this->__createDir(self::ACTUAL_DIR_NAME, $required);
	}
	protected function _createFictitiousDir($required = false) {
		return $this->__createDir(self::FICTITIOUS_DIR_NAME, $required);
	}
	protected function _createDirWithTrailingSlash($required = false) {
		return $this->__createDir(self::ACTUAL_DIR_NAME . '/', $required);
	}

	public function testConstructor() {
		$dir = new Dir(self::ACTUAL_DIR_NAME);
		$this->assertNotNull($dir);
		$this->assertTrue($dir instanceof Dir);
	}

	public function testIdIfNoIdHasBeenSuppliedManually() {
		$dir = $this->_createActualDir();
		$this->assertEquals(self::ACTUAL_DIR_NAME, $dir->id());
	}
	public function testIdWithAsSuppliedIdAndResetItAfterwards() {
		$dir = $this->_createActualDir();

		$dir->id('new_id');
		$this->assertEquals('new_id', $dir->id());

		$dir->id('');
		$this->assertEquals(self::ACTUAL_DIR_NAME, $dir->id());
	}

	public function testDirWithFictitiousDir() {
		$dir = $this->_createFictitiousDir();
		$this->assertEquals(self::FICTITIOUS_DIR_NAME, $dir->dir());
	}
	public function testDirWithActualDir() {
		$dir = $this->_createActualDir();
		$this->assertEquals(self::ACTUAL_DIR_NAME, $dir->dir());
	}

	public function testDirWithTrailingSlashDir() {
		$dir = $this->_createDirWithTrailingSlash();
		$this->assertEquals(self::ACTUAL_DIR_NAME, $dir->dir());
	}

	public function testIfDirIsRequiredThatIsNot() {
		$dir = $this->_createActualDir();
		$this->assertFalse($dir->isRequired());
	}
	public function testIfDirIsRequiredWhenItShouldBe() {
		$dir = $this->_createActualDir(true);
		$this->assertTrue($dir->isRequired());
	}

	public function testParsePath() {
		$this->_testParsePath(self::ACTUAL_DIR_NAME , self::ACTUAL_DIR_NAME);
		$this->_testParsePath(self::ACTUAL_DIR_NAME , self::ACTUAL_DIR_NAME . '/');
		$this->_testParsePath(self::ACTUAL_DIR_NAME , self::ACTUAL_DIR_NAME . '\\');
		$this->_testParsePath(self::ACTUAL_DIR_NAME , self::ACTUAL_DIR_NAME . '\\\\');
		$this->_testParsePath(self::ACTUAL_DIR_NAME , self::ACTUAL_DIR_NAME . '//');
	}
	protected function _testParsePath($expected, $actual) {
		$path = $this->callNonPublicMethod('parsePath', $actual);
		$this->assertEquals($expected, $path);
	}
}