<?php

namespace FQ\Tests;

use FQ\Dirs\ChildDir;
use FQ\Dirs\Dir;
use FQ\Dirs\RootDir;
use FQ\Files;
use PHPUnit_Framework_TestCase;

/**
 * Base test class for PHP Unit testing
 */
abstract class AbstractFQTest extends PHPUnit_Framework_TestCase
{

	/**
	 * The automatically created test FQ instance
	 * (for easy testing and less boilerplate)
	 *
	 * @type Files
	 */
	protected $_fqApp;

	protected $_nonPublicMethodObject;

	const DIR_ABSOLUTE_DEFAULT = __DIR__;
	const DIR_CUSTOM_ID = 'custom_id';
	const ROOT_DIR_ABSOLUTE_DEFAULT = __DIR__;
	const ROOT_DIR_ID_CUSTOM = 'rootDir';
	const CHILD_DIR_DEFAULT = 'child';


	/**
	 * Setup our test
	 * (runs before each test)
	 *
	 * @return void
	 */
	protected function setUp()
	{
		// Create a new FQ app,
		// since we need one pretty much everywhere
		$this->_fqApp = new Files();
	}

	protected function nonPublicMethodObject($object = null) {
		if ($object !== null) {
			$this->_nonPublicMethodObject = $object;
		}
		return $this->_nonPublicMethodObject;
	}

	protected function callNonPublicMethod($name, $args) {
		return $this->callObjectWithNonPublicMethod($this->nonPublicMethodObject(), $name, $args);
	}
	protected function callObjectWithNonPublicMethod($obj, $name, $args) {
		$class = new \ReflectionClass($obj);
		$method = $class->getMethod($name);
		$method->setAccessible(true);

		$args = is_array($args) ? $args : (array) $args;
		return $method->invokeArgs($obj, $args);
	}

	protected function __newDir($id = self::DIR_CUSTOM_ID, $dir = self::DIR_ABSOLUTE_DEFAULT, $required = false) {
		return new Dir($id, $dir, $required);
	}
	protected function _newActualDir($required = true) {
		return $this->__newDir(self::DIR_CUSTOM_ID, self::DIR_ABSOLUTE_DEFAULT, $required);
	}
	protected function _newFictitiousDir($required = true) {
		return $this->__newDir('does_not_exist', $required);
	}

	protected function __newRootDir($id = self::ROOT_DIR_ID_CUSTOM, $absoluteDir = self::ROOT_DIR_ABSOLUTE_DEFAULT, $basePath = null, $required = false) {
		return new RootDir($id, $absoluteDir, $basePath, $required);
	}
	protected function _newActualRootDir($basePath = null, $required = false) {
		return $this->__newRootDir(self::ROOT_DIR_ID_CUSTOM, self::ROOT_DIR_ABSOLUTE_DEFAULT, $basePath, $required);
	}
	protected function _newFictitiousRootDir($required = true) {
		return $this->__newRootDir('does_not_exist', 'does_not_exist', $required);
	}

	protected function __newChildDir($id = self::CHILD_DIR_DEFAULT, $relativePathFromRootDirs = self::CHILD_DIR_DEFAULT, $required = false) {
		return new ChildDir($id, $relativePathFromRootDirs, $required);
	}
	protected function _newActualChildDir($required = true) {
		return $this->__newChildDir(self::CHILD_DIR_DEFAULT, self::CHILD_DIR_DEFAULT, $required);
	}
	protected function _newFictitiousChildDir($required = false) {
		return $this->__newChildDir('does_not_exist', 'does_not_exist', $required);
	}
}
