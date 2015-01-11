<?php
namespace Mw\Metamorph\Tests\Transformation\Sorting;

use Mw\Metamorph\Transformation\Sorting\TopologicalTransformationSorter;
use Mw\Metamorph\Transformation\Sorting\TransformationNode;
use Mw\Metamorph\Transformation\Sorting\TransformationSorter;
use TYPO3\Flow\Tests\UnitTestCase;

/**
 * @covers Mw\Metamorph\Transformation\Sorting\TopologicalTransformationSorter
 * @uses Mw\Metamorph\Transformation\Sorting\TransformationNode
 */
class TopologicalTransformationSorterTest extends UnitTestCase {

	/** @var TransformationNode[] */
	private $graph;

	/** @var TransformationSorter */
	private $sorter;

	public function setUp() {
		$this->getMockBuilder('TransformationStubInterface')
			->setMockClassName('TransformationStub')
			->getMock();

		$last = new TransformationNode('TransformationStub');
		$req1 = new TransformationNode('TransformationStub');
		$req2 = new TransformationNode('TransformationStub');

		$req1->addSuccessor($last);
		$req2->addSuccessor($last);

		$this->graph = [
			'last' => $last,
			'req1' => $req1,
			'req2' => $req2
		];

		$this->sorter = new TopologicalTransformationSorter();
	}

	/**
	 * @test
	 */
	public function graphIsSortedCorrectly() {
		$sorted = $this->sorter->sort($this->graph);

		$this->assertTrue($sorted[2] === $this->graph['last']);
		$this->assertTrue(
			($sorted[0] === $this->graph['req1'] && $sorted[1] === $this->graph['req2']) ||
			($sorted[0] === $this->graph['req2'] && $sorted[1] === $this->graph['req1'])
		);
	}

	/**
	 * @test
	 * @expectedException \Exception
	 */
	public function exceptionIsThrownOnCyclicDependency() {
		$a = new TransformationNode('TransformationStub');
		$b = new TransformationNode('TransformationStub');

		$a->addSuccessor($b);
		$b->addSuccessor($a);

		$this->sorter->sort([$a, $b]);
	}

}