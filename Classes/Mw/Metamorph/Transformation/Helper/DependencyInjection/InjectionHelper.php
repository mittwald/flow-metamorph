<?php
namespace Mw\Metamorph\Transformation\Helper\DependencyInjection;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.Metamorph".          *
 *                                                                        *
 * (C) 2014 Martin Helmich <m.helmich@mittwald.de>                        *
 *          Mittwald CM Service GmbH & Co. KG                             *
 *                                                                        */

use Mw\Metamorph\Transformation\Helper\Annotation\AnnotationRenderer;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;

/**
 * Helper class for adding dependency injections to classes.
 *
 * @package    Mw\Metamorph
 * @subpackage Transformation\Helper\DependencyInjection
 */
class InjectionHelper {

	/**
	 * @var BuilderFactory
	 */
	protected $factory;

	public function __construct() {
		$this->factory = new BuilderFactory();
	}

	/**
	 * Adds a new dependency into a class node.
	 *
	 * @param Class_ $classNode  The class node into which to add a dependency.
	 * @param string $dependency The dependency class (fully-qualified!)
	 * @param string $name       The property name to use for the dependency.
	 * @param bool   $lazy       TRUE to use lazy dependency injection (which is the default).
	 * @return Class_
	 */
	public function injectDependencyIntoClass(Class_ $classNode, $dependency, $name, $lazy = TRUE) {
		$dependency = '\\' . ltrim($dependency, '\\');
		$found      = $this->isDependencyAlreadyPresentInClass($classNode, $dependency, $name, $lastPropertyIndex);

		// Return immediately if dependency is already present.
		if (TRUE === $found) {
			return $classNode;
		}

		$annotation = new AnnotationRenderer('Flow', 'Inject');
		if (FALSE === $lazy) {
			$annotation->addParameter('lazy', FALSE);
		}

		$comment = new Doc("/**\n * @var $dependency\n * " . $annotation->render() . "\n */");

		$property = $this->factory
			->property($name)
			->makeProtected()
			->getNode();

		$property->setAttribute('comments', [$comment]);

		$stmts = $classNode->stmts;
		if (NULL === $lastPropertyIndex) {
			$stmts = array_merge([$property], $classNode->stmts);
		} else {
			array_splice($stmts, $lastPropertyIndex + 1, 0, [$property]);
		}

		$classNode->stmts = $stmts;
		return $classNode;
	}

	/**
	 * @param Class_ $classNode
	 * @param string $dependency
	 * @param string $name
	 * @param int    $lastPropertyIndex
	 * @return array
	 */
	private function isDependencyAlreadyPresentInClass(Class_ $classNode, $dependency, $name, &$lastPropertyIndex) {
		$found             = FALSE;
		$lastPropertyIndex = NULL;

		foreach ($classNode->stmts as $i => $classStmt) {
			if (!$classStmt instanceof Property) {
				continue;
			}

			$lastPropertyIndex = $i;
			foreach ($classStmt->props as $prop) {
				if ($prop->name === $name) {
					$found = TRUE;
				}
			}
		}
		return $found;
	}

} 