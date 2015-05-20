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
		$query = $this->files()->query();
		$this->assertNotNull($query);
		$this->assertTrue($query instanceof FilesQuery);
	}

	public function testIfThereAreNoRootDirsOnConstruct()
	{
		$rootDirs = $this->files()->rootDirs();
		$this->assertEmpty($rootDirs);
		$this->assertTrue(is_array($rootDirs));

		$this->assertEquals(0, $this->files()->totalRootDirs());
	}

	public function testAddRootDir() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals(1, $this->files()->totalRootDirs());
		$this->assertEquals($rootDir, $this->files()->addRootDir($rootDir));
	}

	public function testAddingOneRootDir() {
		$this->_addRootDir();
		$this->assertEquals(1, $this->files()->totalRootDirs());
	}

	public function testAddingMultipleRootDirs() {
		$this->_addRootDirs(2);
		$this->assertEquals(2, $this->files()->totalRootDirs());
	}

	public function testAddingOneRootDirAndRetrieveIt() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals($rootDir, $this->files()->getRootDirByIndex(0));
		$this->assertEquals($rootDir, $this->files()->getRootDirById(AbstractFQTest::ROOT_DIR_DEFAULT_ID));
		$this->assertEquals($rootDir, $this->files()->getRootDir(AbstractFQTest::ROOT_DIR_DEFAULT_ID));
		$this->assertEquals($rootDir, $this->files()->getRootDir($rootDir));
	}

	public function testAddingRootDirAtIndex() {
		$firstRootDir = $this->_addRootDir();
		$secondRootDir = $this->_addRootDir(0);

		$this->assertEquals(2, $this->files()->totalRootDirs());
		$this->assertEquals($firstRootDir, $this->files()->getRootDirByIndex(1));
		$this->assertEquals($secondRootDir, $this->files()->getRootDirByIndex(0));
	}

	public function testAddingOneRootDirAndRetrieveAllPaths() {
		$this->_addRootDir();
		$this->assertEquals(array(AbstractFQTest::ROOT_DIR_DEFAULT_ABSOLUTE_PATH), $this->files()->getRootPaths());
	}

	public function testRemoveRootDir() {
		$rootDir = $this->_addRootDir();
		$this->assertEquals($rootDir, $this->files()->removeRootDir($rootDir));
		$this->assertFalse($this->files()->removeRootDir($rootDir));
	}
	public function testRemoveRootDirById() {
		$this->_addRootDir();
		$this->assertTrue($this->files()->removeRootDirById(self::ROOT_DIR_DEFAULT_ID));
		$this->assertFalse($this->files()->removeRootDirById(self::ROOT_DIR_DEFAULT_ID));
	}
	public function testRemoveRootDirAtIndex() {
		$this->_addRootDir();
		$this->assertTrue($this->files()->removeRootDirAtIndex(0));
		$this->assertFalse($this->files()->removeRootDirAtIndex(0));
	}
	public function testRemoveAllRootDirs() {
		$this->_addRootDir();
		$this->_addRootDir();
		$this->files()->removeAllRootDirs();
		$this->assertEquals(array(), $this->files()->rootDirs());
	}

	public function testContainerRootDir() {
		$rootDir = $this->_addRootDir();
		$this->assertTrue($this->files()->containsRootDir($rootDir));
		$this->assertFalse($this->files()->containsRootDir($this->_newActualRootDirSecond()));
	}


	public function testIfThereAreNoChildDirsOnConstruct()
	{
		$childDirs = $this->files()->childDirs();
		$this->assertEmpty($childDirs);
		$this->assertTrue(is_array($childDirs));

		$this->assertEquals(0, $this->files()->totalChildDirs());
	}

	public function testAddChildDir() {
		$childDir = $this->_addChildDir();
		$this->assertEquals(1, $this->files()->totalChildDirs());
		$this->assertEquals($childDir, $this->files()->addChildDir($childDir));
	}

	public function testContainerChildDir() {
		$childDir = $this->_addChildDir();
		$this->assertTrue($this->files()->containsChildDir($childDir));
		$this->assertFalse($this->files()->containsChildDir($this->_newActualChildDir()));
	}

	public function testAddingOneChildDir() {
		$this->_addChildDir();
		$this->assertEquals(1, $this->files()->totalChildDirs());
	}

	public function testAddingMultipleChildDirs() {
		$this->_addChildDirs(2);
		$this->assertEquals(2, $this->files()->totalChildDirs());
	}

	public function testAddingOneChildDirAndRetrieveIt() {
		$childDir = $this->_addChildDir();
		$this->assertEquals($childDir, $this->files()->getChildDirByIndex(0));
		$this->assertEquals($childDir, $this->files()->getChildDirById(AbstractFQTest::CHILD_DIR_DEFAULT_DIR));
		$this->assertEquals($childDir, $this->files()->getChildDir(AbstractFQTest::CHILD_DIR_DEFAULT_DIR));
		$this->assertEquals($childDir, $this->files()->getChildDir($childDir));
	}

	public function testAddingChildDirAtIndex() {
		$firstChildDir = $this->_addChildDir();
		$secondChildDir = $this->_addChildDir(0);

		$this->assertEquals(2, $this->files()->totalChildDirs());
		$this->assertEquals($firstChildDir, $this->files()->getChildDirByIndex(1));
		$this->assertEquals($secondChildDir, $this->files()->getChildDirByIndex(0));
	}

	public function testRemoveChildDir() {
		$childDir = $this->_addChildDir();
		$this->assertEquals($childDir, $this->files()->removeChildDir($childDir));
		$this->assertFalse($this->files()->removeChildDir($childDir));
	}
	public function testRemoveChildDirById() {
		$this->_addChildDir();
		$this->assertTrue($this->files()->removeChildDirById(self::CHILD_DIR_DEFAULT_ID));
		$this->assertFalse($this->files()->removeChildDirById(self::CHILD_DIR_DEFAULT_ID));
	}
	public function testRemoveChildDirAtIndex() {
		$this->_addChildDir();
		$this->assertTrue($this->files()->removeChildDirAtIndex(0));
		$this->assertFalse($this->files()->removeChildDirAtIndex(0));
	}
	public function testRemoveAllChildDirs() {
		$this->_addChildDir();
		$this->_addChildDir();
		$this->files()->removeAllChildDirs();
		$this->assertEquals(array(), $this->files()->childDirs());
	}

	public function testAddingOneChildDirAndRetrieveAllPaths() {
		$this->_addChildDir();
		$this->assertEquals(array(AbstractFQTest::CHILD_DIR_DEFAULT_DIR), $this->files()->getChildPaths());
	}

	public function testIfFilesIsInvalidAfterAddingRequiredChildDirThatDoNotExist() {
		$childDir = $this->_addChildDir(null, $this->_newFictitiousChildDir());

		// because no root dir has been added, this will return the child directory object and not false
		$this->assertEquals($childDir, $this->files()->getChildDirByIndex(0));
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatExistsAndRequiredChildDirThatDoNotExist() {
		$this->setExpectedException('\FQ\Exceptions\FilesException');
		$this->_addRootDir(null, null, true);
		$this->_addChildDir(null, $this->_newFictitiousChildDir(true));
	}
	public function testIfFilesIsInvalidAfterAddingMultipleRequiredRootDirsThatAllExistAndMultipleRequiredChildDirFromWhichTheLastOneDoesNotExist() {
		$this->setExpectedException('\FQ\Exceptions\FilesException');
		$this->_addRootDirs(2, true);
		$this->_addChildDir(null, null, true);
		$this->_addChildDir(null, $this->_newFictitiousChildDir(true));
	}
	public function testIfFilesIsInvalidAfterAddingRequiredRootDirThatDoesExistsButThrowErrorsIsSetToTrue() {
		$this->setExpectedException('FQ\Exceptions\FilesException');
		$this->_addRootDir(null, $this->__newRootDir('does not exist', null, null, true));
	}

	public function testGetFullPath() {
		$this->setExpectedException('FQ\Exceptions\FilesException', 'Cannot build a full path because either the root directory, the child directory or both are not defined. Root directory id "root1". Child directory id "-"');
		$rootDir = $this->_addRootDir();
		$this->files()->getFullPath($rootDir, null);
	}

	public function testQueryPathWithTwoRootDirs() {
		$files = $this->files();
		$this->_addRootDir();
		$this->_addRootDir(null, $this->_newActualRootDirSecond());
		$this->_addChildDir();
		$this->assertEquals(self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File1.php', $files->queryPath('File1'));
		$this->assertFalse($files->queryPath('does-not-exist'));
	}
	public function testQueryPathWithTwoRootDirsWhenReversed() {
		$files = $this->files();
		$this->_addRootDir();
		$this->_addRootDir(null, $this->_newActualRootDirSecond());
		$this->_addChildDir();
		$this->assertEquals(self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php', $files->queryPath('File1', null, null, true));
		$this->assertFalse($files->queryPath('does-not-exist'));
	}

	public function testLoadFile() {
		$files = $this->files();
		$this->_addRootDir();
		$this->_addChildDir();
		$this->assertTrue($files->loadFile('File2'));
		$this->assertTrue(class_exists('File2'));
		$this->assertFalse($files->loadFile('does-not-exist'));
	}

	public function testLoadFiles() {
		$files = $this->files();
		$this->_addRootDir();
		$this->_addRootDir(null, $this->_newActualRootDirSecond());
		$this->_addChildDir();
		$this->assertTrue($files->loadFiles('bootstrap'));
		$this->assertTrue(defined('BOOTSTRAP_ROOT_1'));
		$this->assertTrue(defined('BOOTSTRAP_ROOT_2'));
		$this->assertFalse($files->loadFiles('does-not-exist'));
	}

	public function testQueryPaths() {
		$files = $this->files();
		$this->_addRootDir();
		$this->_addRootDir(null, $this->_newActualRootDirSecond());
		$this->_addChildDir();
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => self::ROOT_DIR_DEFAULT_ABSOLUTE_PATH . '/child1/File1.php',
			self::ROOT_DIR_SECOND_ID => self::ROOT_DIR_SECOND_ABSOLUTE_PATH . '/child1/File1.php'
		), $files->queryPaths('File1'));
		$this->assertEquals(array(), $files->queryPaths('does-not-exist'));
	}

	public function testFileExist() {
		$files = $this->files();
		$this->_addRootDir();
		$this->_addChildDir();
		$this->assertTrue($files->fileExists('File2'));
		$this->assertFalse($files->fileExists('does-not-exist'));
	}




	protected function _addRootDir($index = null, RootDir $rootDir = null, $required = false) {
		$rootDir = $rootDir !== null ? $rootDir : $this->_newActualRootDir(null, $required);
		return $this->files()->addRootDir($rootDir, $index);
	}
	protected function _addRootDirs($amount, $required = false) {
		while ($amount) {
			$this->_addRootDir(null, null, $required);
			$amount--;
		}
	}

	protected function _addChildDir($index = null, ChildDir $childDir = null, $required = false) {
		$childDir = $childDir !== null ? $childDir : $this->_newActualChildDir($required);
		return $this->files()->addChildDir($childDir, $index);
	}
	protected function _addChildDirs($amount) {
		while ($amount) {
			$this->_addChildDir();
			$amount--;
		}
	}
}