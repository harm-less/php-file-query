<?php

namespace FQ\Tests\Utils;

use FQ\Tests\AbstractFQTest;
use FQ\Utils\Dirs;

class DirsTest extends AbstractFQTest {

	public function testEqualDirs() {
		$this->assertTrue(Dirs::equalDirs(array(), array()));

		$rootDir1 = $this->_newActualRootDir();
		$rootDir2 = $this->_newActualRootDir('id');

		$this->assertFalse(Dirs::equalDirs(array(
			$this->_newActualRootDir()
		), array(
			$this->_newActualRootDir()
		)));

		$this->assertTrue(Dirs::equalDirs(array(
			$rootDir1
		), array(
			$rootDir1
		)));

		$this->assertTrue(Dirs::equalDirs(array(
			$rootDir1,
			$rootDir2
		), array(
			$rootDir1,
			$rootDir2
		)));

		$this->assertFalse(Dirs::equalDirs(array(
			$rootDir2,
			$rootDir1
		), array(
			$rootDir1,
			$rootDir2
		)));
	}

}