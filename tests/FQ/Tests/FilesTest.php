<?php

namespace FQ\Tests;

use FQ\Dirs\RootDir;
use FQ\Files;
use FQ\Query\FilesQuery;

class FilesTest extends AbstractFQTest {

	const ROOT_DIR_ID = 'rootDir';
	const CHILD_DIR_ID = 'childDir';

	public function testConstructor()
	{
		$klein = new Files();
		$this->assertNotNull($klein);
		$this->assertTrue($klein instanceof Files);
	}
	public function testQuery()
	{
		$query = $this->fqApp->query();
		$this->assertNotNull($query);
		$this->assertTrue($query instanceof FilesQuery);
	}

	public function testIfThereAreNoRootDirsOnConstruct()
	{
		$rootDirs = $this->fqApp->rootDirs();
		$this->assertEmpty($rootDirs);
		$this->assertTrue(is_array($rootDirs));

		$this->assertEquals(0, $this->fqApp->totalRootDirs());
	}

	public function testAddingOneRootDir() {
		$rootDir = $this->_addOneRootDir();
		$this->assertEquals(1, $this->fqApp->totalRootDirs());
		$this->assertEquals($rootDir, $this->fqApp->getRootDirByIndex(0));
	}

	public function testAddingOneRootDirAndRetrieveIt() {
		$rootDir = $this->_addOneRootDir();
		$this->assertEquals($rootDir, $this->fqApp->getRootDirByIndex(0));
		$this->assertEquals($rootDir, $this->fqApp->getRootDir(self::ROOT_DIR_ID));
	}


	public function testIfThereAreNoChildDirsOnConstruct()
	{
		$childDirs = $this->fqApp->childDirs();
		$this->assertEmpty($childDirs);
		$this->assertTrue(is_array($childDirs));

		$this->assertEquals(0, $this->fqApp->totalChildDirs());
	}


	protected function _addRootDir($id, $index = null) {
		$rootDir = new RootDir($id, '/');
		$this->fqApp->addRootDir($rootDir, $index);
		return $rootDir;
	}
	protected function _addOneRootDir($index = null) {
		return $this->_addRootDir(self::ROOT_DIR_ID, $index);
	}
}