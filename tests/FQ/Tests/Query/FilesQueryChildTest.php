<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQueryChild;
use FQ\Query\FilesQueryRequirements;

class FilesQueryChildTest extends AbstractFilesQueryTests {


	protected function setUp()
	{
		parent::setUp();

		$this->nonPublicMethodObject($this->queryChild());
	}

	public function testConstructor()
	{
		$filesQueryChild = new FilesQueryChild($this->query(), $this->_newActualChildDir());
		$this->assertNotNull($filesQueryChild);
	}

	public function testReset() {
		$queryChild = $this->queryChild();
		$queryChild->reset();
	}

	public function testSetRootDirs() {
		$firstRootDir = $this->_newActualRootDir();
		$secondRootDir = $this->_newActualRootDirSecond();
		$queryChild = $this->queryChild();
		$queryChild->setRootDirs(array($firstRootDir, $secondRootDir));
		$this->assertEquals(array($firstRootDir, $secondRootDir), $queryChild->getRootDirs());
	}

	public function testGetRootDirsEmpty() {
		$queryChild = $this->queryChild();
		$queryChild->setRootDirs(null);
		$this->assertEquals(array(), $queryChild->getRootDirs());
	}

	public function testQuery() {
		$queryChild = $this->queryChild();
		$this->assertTrue(is_a($queryChild->query(), 'FQ\Query\FilesQuery'));
	}

	public function testFiles() {
		$queryChild = $this->queryChild();
		$this->assertTrue(is_a($queryChild->Files(), 'FQ\Files'));
	}

	public function testChildDir() {
		$queryChild = $this->queryChild();
		$this->assertTrue(is_a($queryChild->childDir(), 'FQ\Dirs\ChildDir'));
	}
}