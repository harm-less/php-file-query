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
		$query = $this->_fqApp->query();
		$this->assertNotNull($query);
		$this->assertTrue($query instanceof FilesQuery);
	}

	public function testIfThereAreNoRootDirsOnConstruct()
	{
		$rootDirs = $this->_fqApp->rootDirs();
		$this->assertEmpty($rootDirs);
		$this->assertTrue(is_array($rootDirs));

		$this->assertEquals(0, $this->_fqApp->totalRootDirs());
	}

	public function testAddRootDir() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals(1, $this->_fqApp->totalRootDirs());
		$this->assertEquals($rootDir, $this->_fqApp->addRootDir($rootDir));
	}

	public function testAddingOneRootDir() {
		$this->_addRootDir();
		$this->assertEquals(1, $this->_fqApp->totalRootDirs());
	}

	public function testAddingMultipleRootDirs() {
		$this->_addRootDirs(2);
		$this->assertEquals(2, $this->_fqApp->totalRootDirs());
	}

	public function testAddingOneRootDirAndRetrieveIt() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals($rootDir, $this->_fqApp->getRootDirByIndex(0));
		$this->assertEquals($rootDir, $this->_fqApp->getRootDirById(AbstractFQTest::ROOT_DIR_ABSOLUTE_DEFAULT));
		$this->assertEquals($rootDir, $this->_fqApp->getRootDir(AbstractFQTest::ROOT_DIR_ABSOLUTE_DEFAULT));
		$this->assertEquals($rootDir, $this->_fqApp->getRootDir($rootDir));

		$this->assertFalse($this->_fqApp->getRootDirByIndex(1));
		$this->assertNull($this->_fqApp->getRootDirById('id that does not exist'));
		$this->assertNull($this->_fqApp->getRootDir('id that does not exist'));
		$this->assertNull($this->_fqApp->getRootDir(null));
	}

	public function testAddingRootDirAtIndex() {
		$firstRootDir = $this->_addRootDir();
		$secondRootDir = $this->_addRootDir(0);

		$this->assertEquals(2, $this->_fqApp->totalRootDirs());
		$this->assertEquals($firstRootDir, $this->_fqApp->getRootDirByIndex(1));
		$this->assertEquals($secondRootDir, $this->_fqApp->getRootDirByIndex(0));
	}

	public function testAddingOneRootDirAndRetrieveAllPaths() {
		$this->_addRootDir();
		$this->assertEquals(array(AbstractFQTest::ROOT_DIR_ABSOLUTE_DEFAULT), $this->_fqApp->getRootPaths());
	}


	public function testIfThereAreNoChildDirsOnConstruct()
	{
		$childDirs = $this->_fqApp->childDirs();
		$this->assertEmpty($childDirs);
		$this->assertTrue(is_array($childDirs));

		$this->assertEquals(0, $this->_fqApp->totalChildDirs());
	}

	public function testAddChildDir() {
		$childDir = $this->_addChildDir();
		$this->assertEquals(1, $this->_fqApp->totalChildDirs());
		$this->assertEquals($childDir, $this->_fqApp->addChildDir($childDir));
	}

	public function testAddingOneChildDir() {
		$this->_addChildDir();
		$this->assertEquals(1, $this->_fqApp->totalChildDirs());
	}

	public function testAddingMultipleChildDirs() {
		$this->_addChildDirs(2);
		$this->assertEquals(2, $this->_fqApp->totalChildDirs());
	}

	public function testAddingOneChildDirAndRetrieveIt() {
		$childDir = $this->_addChildDir();
		$this->assertEquals($childDir, $this->_fqApp->getChildDirByIndex(0));
		$this->assertEquals($childDir, $this->_fqApp->getChildDirById(AbstractFQTest::CHILD_DIR_DEFAULT));
		$this->assertEquals($childDir, $this->_fqApp->getChildDir(AbstractFQTest::CHILD_DIR_DEFAULT));
		$this->assertEquals($childDir, $this->_fqApp->getChildDir($childDir));

		$this->assertFalse($this->_fqApp->getChildDirByIndex(1));
		$this->assertNull($this->_fqApp->getChildDirById('id that does not exist'));
		$this->assertNull($this->_fqApp->getChildDir('id that does not exist'));
		$this->assertNull($this->_fqApp->getChildDir(null));
	}

	public function testAddingChildDirAtIndex() {
		$firstChildDir = $this->_addChildDir();
		$secondChildDir = $this->_addChildDir(0);

		$this->assertEquals(2, $this->_fqApp->totalChildDirs());
		$this->assertEquals($firstChildDir, $this->_fqApp->getChildDirByIndex(1));
		$this->assertEquals($secondChildDir, $this->_fqApp->getChildDirByIndex(0));
	}

	public function testAddingOneChildDirAndRetrieveAllPaths() {
		$this->_addChildDir();
		$this->assertEquals(array(AbstractFQTest::CHILD_DIR_DEFAULT), $this->_fqApp->getChildPaths());
	}

	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatDoNotExist() {
		$rootDir = $this->_addRootDir(null, $this->_newFictitiousRootDir());
		$this->assertFalse($rootDir);
	}
	public function testIfFilesIsInvalidAfterAddingRequiredChildDirThatDoNotExist() {
		$childDir = $this->_addChildDir(null, $this->_newFictitiousChildDir());

		// because no root dir has been added, this will return the child directory object and not false
		$this->assertEquals($childDir, $this->_fqApp->getChildDirByIndex(0));
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatExistsAndRequiredChildDirThatDoNotExist() {
		$rootDir = $this->_addRootDir();
		$childDir = $this->_addChildDir(null, $this->_newFictitiousChildDir());

		$this->assertEquals($rootDir, $this->_fqApp->getRootDirByIndex(0));
		$this->assertFalse($childDir);
	}
	public function testIfFilesIsInvalidAfterAddingMultipleRequiredRootDirsThatAllExistAndMultipleRequiredChildDirFromWhichTheLastOneDoesNotExist() {
		$this->_addRootDirs(2);
		$this->_addChildDir();
		$childDirInvalid = $this->_addChildDir(null, $this->_newFictitiousChildDir());

		$this->assertFalse($childDirInvalid);
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatDoesExistsButThrowErrorsIsSetToTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');

		$this->_fqApp->throwErrors(true);
		$this->_addRootDir(null, $this->__newRootDir('does not exist'));
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatExistsAndRequiredChildDirThatDoNotExistButThrowErrorsIsSetToTrue() {
		$this->setExpectedException('FQ\Exceptions\ExceptionableException');

		$this->_fqApp->throwErrors(true);
		$this->_addRootDir();
		$this->_addChildDir(null, $this->_newFictitiousChildDir());
	}


	protected function _addRootDir($index = null, RootDir $rootDir = null) {
		$rootDir = $rootDir !== null ? $rootDir : $this->_newActualRootDir();
		return $this->_fqApp->addRootDir($rootDir, $index);
	}
	protected function _addRootDirs($amount) {
		while ($amount) {
			$this->_addRootDir();
			$amount--;
		}
	}

	protected function _addChildDir($index = null, ChildDir $childDir = null) {
		$childDir = $childDir !== null ? $childDir : $this->_newActualChildDir();
		return $this->_fqApp->addChildDir($childDir, $index);
	}
	protected function _addChildDirs($amount) {
		while ($amount) {
			$this->_addChildDir();
			$amount--;
		}
	}
}