<?php
namespace Mw\Metamorph\Tests\Transformation\Sorting;

use Mw\Metamorph\Transformation\Sorting\TransformationNode;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * @covers Mw\Metamorph\Transformation\Sorting\TransformationNode
 */
class TransformationNodeTest extends UnitTestCase {

	/**
	 * @test
	 */
	public function constructorAddsDefaultNamespaceWhenClassDoesNotExist() {
		$node = new TransformationNode('AnalyzeClasses');
		$this->assertEquals('Mw\\Metamorph\\Step\\AnalyzeClasses', $node->getClassName());
	}

	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function exceptionIsThrownWhenClassDoesNotExist() {
		new TransformationNode('NonExisting');
	}

	/**
	 * @test
	 */
	public function constructorSetsSettings() {
		$node = new TransformationNode('AnalyzeClasses', ['foo' => 'bar']);
		$this->assertEquals(['foo' => 'bar'], $node->getSettings());
	}

	/**
	 * @test
	 */
	public function predecessorsAreAddedBidirectionally() {
		$a = new TransformationNode('AnalyzeClasses');
		$b = new TransformationNode('AnalyzeClasses');

		$a->addPredecessor($b);

		$this->assertContains($b, $a->getPredecessors());
		$this->assertContains($a, $b->getSuccessors());

		return [$a, $b];
	}

	/**
	 * @test
	 */
	public function predecessorsAreAddedOnlyOnce() {
		$a = new TransformationNode('AnalyzeClasses');
		$b = new TransformationNode('AnalyzeClasses');

		$a->addPredecessor($b);
		$a->addPredecessor($b);

		$this->assertCount(1, $a->getPredecessors());
	}

	/**
	 * @test
	 * @depends predecessorsAreAddedBidirectionally
	 */
	public function predecessorsCanBeRemoved($nodes) {
		$nodes[0]->removePredecessor($nodes[1]);
		$this->assertCount(0, $nodes[0]->getPredecessors());
	}

	/**
	 * @test
	 */
	public function predecessorsCanBeCounted() {
		$a = new TransformationNode('AnalyzeClasses');
		$b = new TransformationNode('AnalyzeClasses');

		$a->addPredecessor($b);

		$this->assertEquals(1, $a->getPredecessorCount());
	}

}