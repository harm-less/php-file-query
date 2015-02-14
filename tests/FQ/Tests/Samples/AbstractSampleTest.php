<?php

namespace FQ\Tests\Samples;

use FQ\Samples\SampleBootstrapper;
use PHPUnit_Framework_TestCase;

/**
 * Base test class for PHP Unit testing
 */
abstract class AbstractSampleTest extends PHPUnit_Framework_TestCase
{

	/**
	 * The automatically created test SampleBootstrapper instance
	 * (for easy testing and less boilerplate)
	 *
	 * @type SampleBootstrapper
	 */
	protected $sample;

	protected $sampleClassName;

	function __construct($sampleClassName) {
		parent::__construct();

		$this->sampleClassName = $sampleClassName;
	}


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
		$className = $this->sampleClassName;
		$this->sample = new $className();
	}
}
