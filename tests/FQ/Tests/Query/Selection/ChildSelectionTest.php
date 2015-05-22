<?php

namespace FQ\Tests\Query\Selection;

use FQ\Query\Selection\ChildSelection;
use FQ\Query\Selection\DirSelection;
use FQ\Tests\Query\AbstractFilesQueryTests;

class ChildSelectionTest extends AbstractFilesQueryTests {

	/**
	 * @var ChildSelection
	 */
	protected $_dirSelection;

	protected function setUp() {
		parent::setUp();

		$this->_dirSelection = new ChildSelection();
		$this->nonPublicMethodObject($this->dirSelection());
	}
	protected function dirSelection() {
		return $this->_dirSelection;
	}

	public function testConstructor() {
		$dirSelection = new ChildSelection();
		$this->assertNotNull($dirSelection);
	}

	public function testCopy() {
		$selection = $this->dirSelection();
		$dir1 = $this->_newActualRootDir();
		$dir2 = $this->_newActualRootDirSecond();
		$selection->excludeDir($dir1);
		$selection->excludeDir($dir2);

		$copy = $selection->copy();
		$this->assertEquals('FQ\Query\Selection\ChildSelection', get_class($copy));
		$this->assertNotEquals($selection, $copy);
		$this->assertEquals(array(), $selection->getIncludedDirsById());
		$this->assertEquals(array(), $selection->getIncludedDirsByDir());
		$this->assertEquals(array(), $selection->getExcludedDirsById());
		$this->assertEquals(array($dir1, $dir2), $selection->getExcludedDirsByDir());
	}
}