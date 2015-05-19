<?php

namespace FQ\Tests\Query;

use FQ\Query\FilesQueryChild;
use FQ\Query\FilesQueryRequirements;

class FilesQueryRequirementsTest extends AbstractFilesQueryTests {

	/**
	 * @var FilesQueryRequirements
	 */
	protected $_requirements;

	const DEFAULT_REQUIREMENT = FilesQueryRequirements::REQUIRE_ONE;
	const SECOND_REQUIREMENT = FilesQueryRequirements::REQUIRE_LAST;
	const CUSTOM_REQUIREMENT_ID = 'custom_requirement';
	const CUSTOM_REQUIREMENT_CALLABLE = 'customRequirementCallableMethod';

	protected function setUp()
	{
		parent::setUp();

		// Create a new requirements instance,
		$this->_requirements = $this->_newRequirements();
		$this->nonPublicMethodObject($this->_requirements);
	}

	public function testConstructor()
	{
		$filesQueryRequirements = new FilesQueryRequirements();
		$this->assertNotNull($filesQueryRequirements);
		$this->assertTrue($filesQueryRequirements instanceof FilesQueryRequirements);
		$this->assertEquals(3, $filesQueryRequirements->countRegisteredRequirements());
		$this->assertTrue($filesQueryRequirements->isRegisteredRequirement(FilesQueryRequirements::REQUIRE_ALL));
		$this->assertTrue($filesQueryRequirements->isRegisteredRequirement(FilesQueryRequirements::REQUIRE_LAST));
		$this->assertTrue($filesQueryRequirements->isRegisteredRequirement(FilesQueryRequirements::REQUIRE_ONE));
	}

	public function testAddRequirements() {
		$addRequirement = $this->_addRequirement();
		$requirements = $this->_getRequirementsInstance();
		$this->assertCount(1, $requirements->requirements());
		$this->assertTrue($requirements->hasRequirement(self::DEFAULT_REQUIREMENT));
		$this->assertTrue($addRequirement);
	}
	public function testAddRequirementWithIdThatIsNotAStringOrInteger() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException', 'A requirement van only be of type integer or string. Provided requirement is of type "array"');
		$this->_addRequirement(array());
	}

	public function testAddRequirementWithRequirementIdThatIsNotRegistered() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException', 'Trying to add a requirement, but it isn\'t registered. Provided requirement "doesNotExist"');
		$this->_addRequirement('doesNotExist');
	}
	public function testAddRequirementByAddingTwoRequirementsWithTheSameName() {
		$this->_addRequirement();
		$secondAddedRequirement = $this->_addRequirement();
		$this->assertFalse($secondAddedRequirement);
	}

	public function testAddRequirementsByAddingTwoRequirementsInAnArray() {
		$requirements = $this->_getRequirementsInstance();
		$requirements->addRequirements(array(self::DEFAULT_REQUIREMENT, self::SECOND_REQUIREMENT));
		$this->assertCount(2, $requirements->requirements());
	}
	public function testAddRequirementsByAddingOneRequirementAsAString() {
		$requirements = $this->_getRequirementsInstance();
		$requirements->addRequirements(self::DEFAULT_REQUIREMENT);
		$this->assertCount(1, $requirements->requirements());
	}

	public function testRemoveRequirement() {
		$this->_addRequirement();
		$requirements = $this->_getRequirementsInstance();
		$removeAttempt = $requirements->removeRequirement(self::DEFAULT_REQUIREMENT);
		$this->assertCount(0, $requirements->requirements());
		$this->assertTrue($removeAttempt);
	}
	public function testRemoveRequirementWithRequirementThatWasNotAdded() {
		$requirements = $this->_getRequirementsInstance();
		$removeAttempt = $requirements->removeRequirement(self::DEFAULT_REQUIREMENT);
		$this->assertCount(0, $requirements->requirements());
		$this->assertFalse($removeAttempt);
	}

	public function testRemoveAll() {
		$this->_addRequirement();
		$requirements = $this->_getRequirementsInstance();
		$requirements->removeAll();
		$this->assertCount(0, $requirements->requirements());
	}

	public function testHasRequirement() {
		$requirements = $this->_getRequirementsInstance();
		$this->assertFalse($requirements->hasRequirement(self::DEFAULT_REQUIREMENT));
		$this->_addRequirement();
		$this->assertTrue($requirements->hasRequirement(self::DEFAULT_REQUIREMENT));
	}

	public function testHasRequirements() {
		$requirements = $this->_getRequirementsInstance();
		$this->assertFalse($requirements->hasRequirements());
		$this->_addRequirement();
		$this->assertTrue($requirements->hasRequirements());
	}

	public function testCountRequirements() {
		$requirements = $this->_getRequirementsInstance();
		$this->assertEquals(0, $requirements->countRequirements());
		$this->_addRequirement();
		$this->assertEquals(1, $requirements->countRequirements());
	}

	public function testDefaultRequirements() {
		$this->assertEquals(array(), $this->_getRequirementsInstance()->requirements());
	}

	public function testRegisterRequirement() {
		$this->assertTrue($this->_registerCustomRequirement());
		$this->assertTrue($this->_getRequirementsInstance()->requirementIsRegistered(self::CUSTOM_REQUIREMENT_ID));
		$this->assertFalse($this->_registerCustomRequirement());
	}
	public function testRegisterRequirementWithUncallableMethodUsingAString() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException', 'Trying to register a requirement via a string but the requirement isn\'t callable. Method name "nonExistingMethod"');
		$this->_registerCustomRequirement('nonExistingMethod', 'nonExistingMethod');
	}
	public function testRegisterRequirementWithUncallableMethodUsingAnArray() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException', 'Trying to register a requirement but the requirement isn\'t callable. Class name "FQ\Tests\Query\FilesQueryRequirementsTest", method name "nonExistingMethod"');
		$this->_registerCustomRequirement('nonExistingMethod', array($this, 'nonExistingMethod'));
	}
	public function testRegisterRequirementUsingAnUnknownDataType() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException', 'Trying to register a requirement but the requirement\'s callable isn\'t a known data-type. Must be a string or an array. Type given "integer"');
		$this->_registerCustomRequirement('nonExistingMethod', 1);
	}

	public function testCountRegisteredRequirements() {
		$requirements = $this->_getRequirementsInstance();
		$this->assertEquals(3, $requirements->countRegisteredRequirements());
		$this->_registerCustomRequirement();
		$this->assertEquals(4, $requirements->countRegisteredRequirements());
	}

	public function testRequirementIsRegistered() {
		$requirements = $this->_getRequirementsInstance();
		$this->assertFalse($requirements->requirementIsRegistered(self::CUSTOM_REQUIREMENT_ID));
		$this->_registerCustomRequirement();
		$this->assertTrue($requirements->requirementIsRegistered(self::CUSTOM_REQUIREMENT_ID));
	}

	public function testRequirementAtLeastOneSuccess() {
		$this->runQuery();
		$success = $this->callNonPublicMethod('requirementAtLeastOne', array($this->query()));
		$this->assertTrue($success);
	}
	public function testRequirementAtLeastOneFailure() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException');
		$this->runQuery('nonExistentFile');
		$this->callNonPublicMethod('requirementAtLeastOne', array($this->query()));
	}

	public function testRequirementLastSuccess() {
		$this->runQuery();
		$success = $this->callNonPublicMethod('requirementLast', array($this->query()));
		$this->assertTrue($success);
	}
	public function testRequirementLastFailure() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException');
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$this->runQuery();
		$this->callNonPublicMethod('requirementLast', array($this->query()));
	}

	public function testRequirementAllSuccess() {
		$this->runQuery();
		$success = $this->callNonPublicMethod('requirementAll', array($this->query()));
		$this->assertTrue($success);
	}
	public function testRequirementAllFailure() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException');
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$this->runQuery();
		$this->callNonPublicMethod('requirementAll', array($this->query()));
	}

	public function testTryRequirement() {
		$requirements = $this->_getRequirementsInstance();
		$this->runQuery();
		$result = $requirements->tryRequirement(FilesQueryRequirements::REQUIRE_ONE, $this->query());
		$this->assertTrue($result);
	}
	public function testTryRequirementWithUnknownId() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException', 'Trying to call a requirement, but it isn\'t registered. Provided requirement "unknownId"');
		$requirements = $this->_getRequirementsInstance();
		$this->runQuery();
		$requirements->tryRequirement('unknownId', $this->query());
	}
	public function testTryRequirementFailure() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException');
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$this->runQuery();
		$requirements = $this->_getRequirementsInstance();
		throw $requirements->tryRequirement(FilesQueryRequirements::REQUIRE_ALL, $this->query());
	}

	public function testMeetsRequirementsWithoutAnyActiveRequirements() {
		$requirements = $this->_getRequirementsInstance();
		$this->assertTrue($requirements->meetsRequirements($this->query()));
	}
	public function testMeetsRequirementsWithOneRequirement() {
		$requirements = $this->_getRequirementsInstance();
		$requirements->addRequirement(FilesQueryRequirements::REQUIRE_ONE);
		$this->runQuery();
		$this->assertTrue($requirements->meetsRequirements($this->query()));
	}
	public function testMeetsRequirementsWithOneRequirementThatWillFail() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException');
		$requirements = $this->_getRequirementsInstance();
		$requirements->addRequirement(FilesQueryRequirements::REQUIRE_ALL);
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$this->runQuery();
		$this->assertTrue($requirements->meetsRequirements($this->query()));
	}
	public function testMeetsRequirementsWithOneRequirementThatWillFailButIsNotThrowingAnError() {
		$this->setExpectedException('\FQ\Exceptions\FileQueryRequirementsException');
		$requirements = $this->_getRequirementsInstance();
		$requirements->addRequirement(FilesQueryRequirements::REQUIRE_ALL);
		$this->files()->addRootDir($this->_newActualRootDirSecond());
		$this->runQuery();
		throw $requirements->meetsRequirements($this->query(), false);
	}

	/**
	 * @return FilesQueryRequirements
	 */
	protected function _newRequirements() {
		return new FilesQueryRequirements();
	}

	protected function _getRequirementsInstance() {
		return $this->_requirements;
	}

	protected function _addRequirement($id = self::DEFAULT_REQUIREMENT) {
		return $this->_getRequirementsInstance()->addRequirement($id);
	}

	protected function _registerCustomRequirement($id = self::CUSTOM_REQUIREMENT_ID, $callable = null) {
		return $this->_getRequirementsInstance()->registerRequirement($id, $callable === null ? array($this, self::CUSTOM_REQUIREMENT_CALLABLE) : $callable);
	}

	public function customRequirementCallableMethod(FilesQueryChild $child) {
		return true;
	}
}