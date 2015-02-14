<?php

namespace FQ\Tests;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Files;
use FQ\Query\FilesQuery;

class FilesTest extends AbstractFQTest {

	public function testConstructor()
	{
		$files = new Files();
		$this->assertNotNull($files);
		$this->assertTrue($files instanceof Files);
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

	public function testAddRootDir() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals(1, $this->fqApp->totalRootDirs());
		$this->assertEquals($rootDir, $this->fqApp->addRootDir($rootDir));
	}

	public function testAddingOneRootDir() {
		$this->_addRootDir();
		$this->assertEquals(1, $this->fqApp->totalRootDirs());
	}

	public function testAddingMultipleRootDirs() {
		$this->_addRootDirs(2);
		$this->assertEquals(2, $this->fqApp->totalRootDirs());
	}

	public function testAddingOneRootDirAndRetrieveIt() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals($rootDir, $this->fqApp->getRootDirByIndex(0));
		$this->assertEquals($rootDir, $this->fqApp->getRootDirById(AbstractFQTest::ROOT_DIR_ABSOLUTE_DEFAULT));
		$this->assertEquals($rootDir, $this->fqApp->getRootDir(AbstractFQTest::ROOT_DIR_ABSOLUTE_DEFAULT));
		$this->assertEquals($rootDir, $this->fqApp->getRootDir($rootDir));

		$this->assertFalse($this->fqApp->getRootDirByIndex(1));
		$this->assertNull($this->fqApp->getRootDirById('id that does not exist'));
		$this->assertNull($this->fqApp->getRootDir('id that does not exist'));
		$this->assertNull($this->fqApp->getRootDir(null));
	}

	public function testAddingRootDirAtIndex() {
		$firstRootDir = $this->_addRootDir();
		$secondRootDir = $this->_addRootDir(0);

		$this->assertEquals(2, $this->fqApp->totalRootDirs());
		$this->assertEquals($firstRootDir, $this->fqApp->getRootDirByIndex(1));
		$this->assertEquals($secondRootDir, $this->fqApp->getRootDirByIndex(0));
	}

	public function testAddingOneRootDirAndRetrieveAllPaths() {
		$this->_addRootDir();
		$this->assertEquals(array(AbstractFQTest::ROOT_DIR_ABSOLUTE_DEFAULT), $this->fqApp->getRootPaths());
	}


	public function testIfThereAreNoChildDirsOnConstruct()
	{
		$childDirs = $this->fqApp->childDirs();
		$this->assertEmpty($childDirs);
		$this->assertTrue(is_array($childDirs));

		$this->assertEquals(0, $this->fqApp->totalChildDirs());
	}

	public function testAddChildDir() {
		$childDir = $this->_addChildDir();
		$this->assertEquals(1, $this->fqApp->totalChildDirs());
		$this->assertEquals($childDir, $this->fqApp->addChildDir($childDir));
	}

	public function testAddingOneChildDir() {
		$this->_addChildDir();
		$this->assertEquals(1, $this->fqApp->totalChildDirs());
	}

	public function testAddingMultipleChildDirs() {
		$this->_addChildDirs(2);
		$this->assertEquals(2, $this->fqApp->totalChildDirs());
	}

	public function testAddingOneChildDirAndRetrieveIt() {
		$childDir = $this->_addChildDir();
		$this->assertEquals($childDir, $this->fqApp->getChildDirByIndex(0));
		$this->assertEquals($childDir, $this->fqApp->getChildDirById(AbstractFQTest::CHILD_DIR_RELATIVE_PATH_FROM_ROOT_DIR));
		$this->assertEquals($childDir, $this->fqApp->getChildDir(AbstractFQTest::CHILD_DIR_RELATIVE_PATH_FROM_ROOT_DIR));
		$this->assertEquals($childDir, $this->fqApp->getChildDir($childDir));

		$this->assertFalse($this->fqApp->getChildDirByIndex(1));
		$this->assertNull($this->fqApp->getChildDirById('id that does not exist'));
		$this->assertNull($this->fqApp->getChildDir('id that does not exist'));
		$this->assertNull($this->fqApp->getChildDir(null));
	}

	public function testAddingChildDirAtIndex() {
		$firstChildDir = $this->_addChildDir();
		$secondChildDir = $this->_addChildDir(0);

		$this->assertEquals(2, $this->fqApp->totalChildDirs());
		$this->assertEquals($firstChildDir, $this->fqApp->getChildDirByIndex(1));
		$this->assertEquals($secondChildDir, $this->fqApp->getChildDirByIndex(0));
	}

	public function testAddingOneChildDirAndRetrieveAllPaths() {
		$this->_addChildDir();
		$this->assertEquals(array(AbstractFQTest::CHILD_DIR_RELATIVE_PATH_FROM_ROOT_DIR), $this->fqApp->getChildPaths());
	}

	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatDoNotExist() {
		$rootDir = $this->_addRootDir(null, $this->_newRootDir('does not exist'));
		$this->assertFalse($rootDir);
	}
	public function testIfFilesIsInvalidAfterAddingRequiredChildDirThatDoNotExist() {
		$childDir = $this->_addChildDir(null, $this->_newChildDir('does not exist'));

		// because no root dir has been added, this will return the child directory object and not false
		$this->assertEquals($childDir, $this->fqApp->getChildDirByIndex(0));
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatExistsAndRequiredChildDirThatDoNotExist() {
		$rootDir = $this->_addRootDir();
		$childDir = $this->_addChildDir(null, $this->_newChildDir('does not exist'));

		$this->assertEquals($rootDir, $this->fqApp->getRootDirByIndex(0));
		$this->assertFalse($childDir);
	}
	public function testIfFilesIsInvalidAfterAddingMultipleRequiredRootDirsThatAllExistAndMultipleRequiredChildDirFromWhichTheLastOneDoesNotExist() {
		$this->_addRootDirs(2);
		$this->_addChildDir();
		$childDirInvalid = $this->_addChildDir(null, $this->_newChildDir('does not exist'));

		$this->assertFalse($childDirInvalid);
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatDoesExistsButThrowErrorsIsSetToTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');

		$this->fqApp->throwErrors(true);
		$this->_addRootDir(null, $this->_newRootDir('does not exist'));
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatExistsAndRequiredChildDirThatDoNotExistButThrowErrorsIsSetToTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');

		$this->fqApp->throwErrors(true);
		$this->_addRootDir();
		$this->_addChildDir(null, $this->_newChildDir('does not exist'));
	}


	protected function _addRootDir($index = null, RootDir $rootDir = null) {
		$rootDir = $rootDir !== null ? $rootDir : $this->_newRootDir();
		return $this->fqApp->addRootDir($rootDir, $index);
	}
	protected function _addRootDirs($amount) {
		while ($amount) {
			$this->_addRootDir();
			$amount--;
		}
	}

	protected function _addChildDir($index = null, ChildDir $childDir = null) {
		$childDir = $childDir !== null ? $childDir : $this->_newChildDir();
		return $this->fqApp->addChildDir($childDir, $index);
	}
	protected function _addChildDirs($amount) {
		while ($amount) {
			$this->_addChildDir();
			$amount--;
		}
	}
}