<?php

namespace FQ\Tests;

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
}
