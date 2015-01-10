<?php
namespace Mw\Metamorph\Tests\Transformation\Sorting;

use Mw\Metamorph\Transformation\Sorting\TransformationGraphBuilder;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * @covers Mw\Metamorph\Transformation\Sorting\TransformationGraphBuilder
 * @uses Mw\Metamorph\Transformation\Sorting\TransformationNode
 */
class TransformationGraphBuilderTest extends UnitTestCase {

	/** @var TransformationGraphBuilder */
	protected $builder;

	public function setUp() {
		$this->getMockBuilder('TransformationStubInterface')
			->setMockClassName('TransformationStub')
			->getMock();

		$this->builder = new TransformationGraphBuilder(
			[
				'first'            => [
					'name'     => 'TransformationStub',
					'settings' => ['foo' => 'bar']
				],
				'second'           => [
					'name'      => 'TransformationStub',
					'dependsOn' => ['first']
				],
				'first_and_a_half' => [
					'name'       => 'TransformationStub',
					'requiredBy' => ['second']
				]
			]
		);
	}

	/**
	 * @test
	 */
	public function nodesAreBuildForEachSettingItem() {
		$nodes = $this->builder->build();

		$this->assertCount(3, $nodes);
		$this->assertArrayHasKey('first', $nodes);
		$this->assertArrayHasKey('first_and_a_half', $nodes);
		$this->assertArrayHasKey('second', $nodes);
	}

	/**
	 * @test
	 * @depends nodesAreBuildForEachSettingItem
	 */
	public function classNamesAreSet() {
		$nodes = $this->builder->build();
		$this->assertEquals('TransformationStub', $nodes['first']->getClassName());
	}

	/**
	 * @test
	 * @depends nodesAreBuildForEachSettingItem
	 */
	public function successorsAreSetCorrectly() {
		$nodes = $this->builder->build();
		$this->assertContains($nodes['second'], $nodes['first']->getSuccessors());
	}

	/**
	 * @test
	 * @depends nodesAreBuildForEachSettingItem
	 */
	public function successorsAreSetCorrectlyInReverseRequirement() {
		$nodes = $this->builder->build();
		$this->assertContains($nodes['second'], $nodes['first_and_a_half']->getSuccessors());
	}

	/**
	 * @test
	 * @depends nodesAreBuildForEachSettingItem
	 */
	public function predecessorsAreSetCorrectly() {
		$nodes = $this->builder->build();
		$this->assertContains($nodes['first'], $nodes['second']->getPredecessors());
	}

	/**
	 * @test
	 * @depends nodesAreBuildForEachSettingItem
	 */
	public function predecessorsAreSetCorrectlyInReverseRequirement() {
		$nodes = $this->builder->build();
		$this->assertContains($nodes['first_and_a_half'], $nodes['second']->getPredecessors());
	}

	/**
	 * @test
	 * @depends nodesAreBuildForEachSettingItem
	 */
	public function settingsAreAssigned() {
		$nodes = $this->builder->build();
		$this->assertEquals(['foo' => 'bar'], $nodes['first']->getSettings());
	}
}