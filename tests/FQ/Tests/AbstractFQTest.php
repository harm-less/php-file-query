<?php

namespace FQ\Tests;

use FQ\Dirs\ChildDir;
use FQ\Dirs\RootDir;
use FQ\Files;
use PHPUnit_Framework_TestCase;

/**
 * AbstractKleinTest
 *
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
	protected $fqApp;

	const ROOT_DIR_ABSOLUTE_DEFAULT = __DIR__;
	const ROOT_DIR_ID_CUSTOM = 'rootDir';
	const CHILD_DIR_RELATIVE_PATH_FROM_ROOT_DIR = 'child';


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
		$this->fqApp = new Files();
	}

	protected function _newRootDir($absoluteDir = self::ROOT_DIR_ABSOLUTE_DEFAULT, $basePath = null, $required = true) {
		return new RootDir($absoluteDir, $basePath, $required);
	}

	protected function _newChildDir($relativePathFromRootDirs = self::CHILD_DIR_RELATIVE_PATH_FROM_ROOT_DIR, $required = true) {
		return new ChildDir($relativePathFromRootDirs, $required);
	}
}
