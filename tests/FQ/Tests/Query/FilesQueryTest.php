<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQuery;

class FilesQueryTest extends AbstractFilesQueryTests {

	public function testConstructor()
	{
		$files = new FilesQuery($this->_fqApp);
		$this->assertNotNull($files);
		$this->assertTrue($files instanceof FilesQuery);
	}

	public function testReset() {
		$files = new FilesQuery($this->_fqApp);
		$files->reset();

		$this->assertFalse($files->requirements()->hasRequirements());

		$filters = $files->filters();
		$this->assertEquals(1, count($filters));
		$this->assertEquals(FilesQuery::FILTER_EXISTING, $filters[0]);

		$this->assertNull($files->queriedFileName());
		$this->assertFalse($files->isReversed());
		$this->assertFalse($files->hasRun());
	}


}