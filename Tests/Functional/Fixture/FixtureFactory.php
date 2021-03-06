<?php
namespace Flowpack\Behat\Tests\Functional\Fixture;

/*                                                                   *
 * This script belongs to the Flow package "Flowpack.Behat".         *
 *                                                                   */

use Neos\Flow\Annotations as Flow;

/**
 * Base test fixture factory
 */
abstract class FixtureFactory {

	/**
	 * @var string
	 */
	protected $baseType = NULL;

	/**
	 *
	 * @var array
	 */
	protected $fixtureDefinitions = array(
	);

	/**
	 * @Flow\Inject
	 * @var \Neos\Flow\Persistence\PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 *
	 * @param string $objectName
	 * @param array $overrideProperties
	 * @param boolean $addObjectToPersistence
	 * @return object
	 */
	public function buildObject($objectName, $overrideProperties = array(), $addObjectToPersistence = FALSE) {
		if (!isset($this->fixtureDefinitions[$objectName])) {
			throw new \Exception('Object name ' . $objectName . ' not configured in fixture definitions');
		}
		$properties = \Neos\Utility\Arrays::arrayMergeRecursiveOverrule($this->fixtureDefinitions[$objectName], $overrideProperties);
		$className = isset($properties['__type']) ? $properties['__type'] : $this->baseType;
		unset($properties['__type']);

		$object = new $className();
		foreach ($properties as $propertyName => $propertyValue) {
			if (\Neos\Utility\ObjectAccess::isPropertySettable($object, $propertyName)) {
				\Neos\Utility\ObjectAccess::setProperty($object, $propertyName, $propertyValue);
			}
		}

		$this->setCustomProperties($object, $properties, $addObjectToPersistence);

		if ($addObjectToPersistence) {
			$this->addObjectToPersistence($object);
		}

		return $object;
	}

	/**
	 * @param object $object
	 * @return void
	 */
	protected function addObjectToPersistence($object) {
		$this->persistenceManager->add($object);
	}

	/**
	 *
	 * @param string $objectName
	 * @param array $overrideProperties
	 * @return object
	 */
	public function createObject($objectName, $overrideProperties = array()) {
		$object = $this->buildObject($objectName, $overrideProperties, TRUE);
		return $object;
	}

	/**
	 *
	 * @param string $methodName
	 * @param array $arguments
	 * @return object
	 */
	public function __call($methodName, array $arguments) {
		if (substr($methodName, 0, 5) === 'build' && strlen($methodName) > 6) {
			$objectName = strtolower(substr($methodName, 5, 1)) . substr($methodName, 6);
			$overrideProperties = isset($arguments[0]) ? $arguments[0] : array();
			return $this->buildObject($objectName, $overrideProperties);
		} elseif (substr($methodName, 0, 6) === 'create' && strlen($methodName) > 7) {
			$objectName = strtolower(substr($methodName, 6, 1)) . substr($methodName, 7);
			$overrideProperties = isset($arguments[0]) ? $arguments[0] : array();
			return $this->createObject($objectName, $overrideProperties);
		}
		trigger_error('Call to undefined method ' . get_class($this) . '::' . $methodName, E_USER_ERROR);
		return NULL;
	}

	/**
	 * Overwrite to implement own property definitions
	 *
	 * @param object $object
	 * @param array $properties
	 * @param boolean $addObjectToPersistence
	 */
	protected function setCustomProperties($object, $properties, $addObjectToPersistence) {}

	/**
	 * Reset this fixture factory
	 *
	 * Implement in custom factories to reset instance caches.
	 *
	 * @return void
	 */
	public function reset() {

	}

}
?>