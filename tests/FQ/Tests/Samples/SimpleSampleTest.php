<?php

namespace FQ\Tests\Samples;

class SimpleSampleTest extends AbstractSampleTest {

	function __construct() {
		parent::__construct('\\FQ\\Samples\\Simple');
	}

	/**
	 * @return \FQ\Samples\Simple
	 */
	public function sample() {
		return $this->_sample;
	}

	public function testQueryFile1FromChild1() {
		$this->assertEquals(array(
			self::ROOT_DIR_SECOND_ID => $this->sample()->root() . 'root2/child1/File1.php',
			self::ROOT_DIR_DEFAULT_ID => $this->sample()->root() . 'root1/child1/File1.php'
		), $this->sample()->queryFile1FromChild1());
	}

	public function testQueryFile1FromRoot1AndFromChild1() {
		$this->assertEquals(array(
			self::ROOT_DIR_DEFAULT_ID => $this->sample()->root() . 'root1/child1/File1.php'
		), $this->sample()->queryFile1FromRoot1AndFromChild1());
	}

	public function testQueryFile1InReverse(){
		$paths = $this->sample()->queryFile1InReverse();
		$index = 0;
		foreach ($paths as $rootDirId => $childPaths) {
			if ($index === 0) {
				$this->assertEquals(self::ROOT_DIR_SECOND_ID, $rootDirId);
				$this->assertEquals($this->sample()->root() . 'root2/child1/File1.php', $childPaths[0]);
				$this->assertEquals($this->sample()->root() . 'root2/child2/File1.php', $childPaths[1]);
			}
			else if ($index === 1) {
				$this->assertEquals(self::ROOT_DIR_DEFAULT_ID, $rootDirId);
				$this->assertEquals($this->sample()->root() . 'root1/child1/File1.php', $childPaths[0]);
				$this->assertEquals($this->sample()->root() . 'root1/child2/File1.php', $childPaths[1]);
			}
			$index++;
		}
	}

}