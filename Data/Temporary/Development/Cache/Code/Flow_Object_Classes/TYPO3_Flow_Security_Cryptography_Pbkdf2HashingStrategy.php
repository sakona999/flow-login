<?php 
namespace TYPO3\Flow\Security\Cryptography;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * A PBKDF2 based password hashing strategy
 *
 */
class Pbkdf2HashingStrategy_Original implements \TYPO3\Flow\Security\Cryptography\PasswordHashingStrategyInterface {

	/**
	 * Length of the dynamic random salt to generate in bytes
	 * @var integer
	 */
	protected $dynamicSaltLength;

	/**
	 * Hash iteration count, high counts (>10.000) make brute-force attacks unfeasible
	 * @var integer
	 */
	protected $iterationCount;

	/**
	 * Derived key length
	 * @var integer
	 */
	protected $derivedKeyLength;

	/**
	 * Hash algorithm to use, see hash_algos()
	 * @var string
	 */
	protected $algorithm;

	/**
	 * Construct a PBKDF2 hashing strategy with the given parameters
	 *
	 * @param integer $dynamicSaltLength Length of the dynamic random salt to generate in bytes
	 * @param integer $iterationCount Hash iteration count, high counts (>10.000) make brute-force attacks unfeasible
	 * @param integer $derivedKeyLength Derived key length
	 * @param string $algorithm Hash algorithm to use, see hash_algos()
	 */
	public function __construct($dynamicSaltLength, $iterationCount, $derivedKeyLength, $algorithm) {
		$this->dynamicSaltLength = $dynamicSaltLength;
		$this->iterationCount = $iterationCount;
		$this->derivedKeyLength = $derivedKeyLength;
		$this->algorithm = $algorithm;
	}

	/**
	 * Hash a password for storage using PBKDF2 and the configured parameters.
	 * Will use a combination of a random dynamic salt and the given static salt.
	 *
	 * @param string $password Cleartext password that should be hashed
	 * @param string $staticSalt Static salt that will be appended to the random dynamic salt
	 * @return string A Base64 encoded string with the derived key (hashed password) and dynamic salt
	 */
	public function hashPassword($password, $staticSalt = NULL) {
		$dynamicSalt = \TYPO3\Flow\Utility\Algorithms::generateRandomBytes($this->dynamicSaltLength);
		$result = \TYPO3\Flow\Security\Cryptography\Algorithms::pbkdf2($password, $dynamicSalt . $staticSalt, $this->iterationCount, $this->derivedKeyLength, $this->algorithm);
		return base64_encode($dynamicSalt) . ',' . base64_encode($result);
	}

	/**
	 * Validate a password against a derived key (hashed password) and salt using PBKDF2.
	 * Iteration count and algorithm have to match the parameters when generating the derived key.
	 *
	 * @param string $password The cleartext password
	 * @param string $hashedPasswordAndSalt The derived key and salt in Base64 encoding as returned by hashPassword for verification
	 * @param string $staticSalt Static salt that will be appended to the dynamic salt
	 * @return boolean TRUE if the given password matches the hashed password
	 * @throws \InvalidArgumentException
	 */
	public function validatePassword($password, $hashedPasswordAndSalt, $staticSalt = NULL) {
		$parts = explode(',', $hashedPasswordAndSalt);
		if (count($parts) !== 2) {
			throw new \InvalidArgumentException('The derived key with salt must contain a salt, separated with a comma from the derived key', 1306172911);
		}
		$dynamicSalt = base64_decode($parts[0]);
		$derivedKey = base64_decode($parts[1]);
		$derivedKeyLength = strlen($derivedKey);
		return $derivedKey === \TYPO3\Flow\Security\Cryptography\Algorithms::pbkdf2($password, $dynamicSalt . $staticSalt, $this->iterationCount, $derivedKeyLength, $this->algorithm);
	}

}
namespace TYPO3\Flow\Security\Cryptography;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * A PBKDF2 based password hashing strategy
 */
class Pbkdf2HashingStrategy extends Pbkdf2HashingStrategy_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 * @param integer $dynamicSaltLength Length of the dynamic random salt to generate in bytes
	 * @param integer $iterationCount Hash iteration count, high counts (>10.000) make brute-force attacks unfeasible
	 * @param integer $derivedKeyLength Derived key length
	 * @param string $algorithm Hash algorithm to use, see hash_algos()
	 */
	public function __construct() {
		$arguments = func_get_args();
		if (get_class($this) === 'TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy', $this);

		if (!array_key_exists(0, $arguments)) $arguments[0] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getSettingsByPath(explode('.', 'TYPO3.Flow.security.cryptography.Pbkdf2HashingStrategy.dynamicSaltLength'));
		if (!array_key_exists(1, $arguments)) $arguments[1] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getSettingsByPath(explode('.', 'TYPO3.Flow.security.cryptography.Pbkdf2HashingStrategy.iterationCount'));
		if (!array_key_exists(2, $arguments)) $arguments[2] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getSettingsByPath(explode('.', 'TYPO3.Flow.security.cryptography.Pbkdf2HashingStrategy.derivedKeyLength'));
		if (!array_key_exists(3, $arguments)) $arguments[3] = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getSettingsByPath(explode('.', 'TYPO3.Flow.security.cryptography.Pbkdf2HashingStrategy.algorithm'));
		if (!array_key_exists(0, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $dynamicSaltLength in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
		if (!array_key_exists(1, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $iterationCount in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
		if (!array_key_exists(2, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $derivedKeyLength in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
		if (!array_key_exists(3, $arguments)) throw new \TYPO3\Flow\Object\Exception\UnresolvedDependenciesException('Missing required constructor argument $algorithm in class ' . __CLASS__ . '. Please check your calling code and Dependency Injection configuration.', 1296143787);
		call_user_func_array('parent::__construct', $arguments);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy', $this);

	if (property_exists($this, 'Flow_Persistence_RelatedEntities') && is_array($this->Flow_Persistence_RelatedEntities)) {
		$persistenceManager = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface');
		foreach ($this->Flow_Persistence_RelatedEntities as $entityInformation) {
			if($entityInformation['entityType'] === 'TYPO3\Flow\Resource\ResourcePointer') continue;
			$entity = $persistenceManager->getObjectByIdentifier($entityInformation['identifier'], $entityInformation['entityType'], TRUE);
			if (isset($entityInformation['entityPath'])) {
				$this->$entityInformation['propertyName'] = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$entityInformation['propertyName'], $entityInformation['entityPath'], $entity);
			} else {
				$this->$entityInformation['propertyName'] = $entity;
			}
		}
		unset($this->Flow_Persistence_RelatedEntities);
	}
			}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __sleep() {
		$result = NULL;
		$this->Flow_Object_PropertiesToSerialize = array();
	$reflectionService = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Reflection\ReflectionService');
	$reflectedClass = new \ReflectionClass('TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
		if (is_array($this->$propertyName) || (is_object($this->$propertyName) && ($this->$propertyName instanceof \ArrayObject || $this->$propertyName instanceof \SplObjectStorage ||$this->$propertyName instanceof \Doctrine\Common\Collections\Collection))) {
			if (count($this->$propertyName) > 0) {
				foreach ($this->$propertyName as $key => $value) {
					$this->searchForEntitiesAndStoreIdentifierArray((string)$key, $value, $propertyName);
				}
			}
		}
		if (is_object($this->$propertyName) && !$this->$propertyName instanceof \Doctrine\Common\Collections\Collection) {
			if ($this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($this->$propertyName);
			} else {
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Flow\Security\Cryptography\Pbkdf2HashingStrategy', $propertyName, 'var');
				if (count($varTagValues) > 0) {
					$className = trim($varTagValues[0], '\\');
				}
				if (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->isRegistered($className) === FALSE) {
					$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($this->$propertyName));
				}
			}
			if ($this->$propertyName instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($this->$propertyName) || $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
				if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
					$this->Flow_Persistence_RelatedEntities = array();
					$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
				}
				$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($this->$propertyName);
				if (!$identifier && $this->$propertyName instanceof \Doctrine\ORM\Proxy\Proxy) {
					$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($this->$propertyName, '_identifier', TRUE));
				}
				$this->Flow_Persistence_RelatedEntities[$propertyName] = array(
					'propertyName' => $propertyName,
					'entityType' => $className,
					'identifier' => $identifier
				);
				continue;
			}
			if ($className !== FALSE && (\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getScope($className) === \TYPO3\Flow\Object\Configuration\Configuration::SCOPE_SINGLETON || $className === 'TYPO3\Flow\Object\DependencyInjection\DependencyProxy')) {
				continue;
			}
		}
		$this->Flow_Object_PropertiesToSerialize[] = $propertyName;
	}
	$result = $this->Flow_Object_PropertiesToSerialize;
		return $result;
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 private function searchForEntitiesAndStoreIdentifierArray($path, $propertyValue, $originalPropertyName) {

		if (is_array($propertyValue) || (is_object($propertyValue) && ($propertyValue instanceof \ArrayObject || $propertyValue instanceof \SplObjectStorage))) {
			foreach ($propertyValue as $key => $value) {
				$this->searchForEntitiesAndStoreIdentifierArray($path . '.' . $key, $value, $originalPropertyName);
			}
		} elseif ($propertyValue instanceof \TYPO3\Flow\Persistence\Aspect\PersistenceMagicInterface && !\TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->isNewObject($propertyValue) || $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
			if (!property_exists($this, 'Flow_Persistence_RelatedEntities') || !is_array($this->Flow_Persistence_RelatedEntities)) {
				$this->Flow_Persistence_RelatedEntities = array();
				$this->Flow_Object_PropertiesToSerialize[] = 'Flow_Persistence_RelatedEntities';
			}
			if ($propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$className = get_parent_class($propertyValue);
			} else {
				$className = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->getObjectNameByClassName(get_class($propertyValue));
			}
			$identifier = \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->get('TYPO3\Flow\Persistence\PersistenceManagerInterface')->getIdentifierByObject($propertyValue);
			if (!$identifier && $propertyValue instanceof \Doctrine\ORM\Proxy\Proxy) {
				$identifier = current(\TYPO3\Flow\Reflection\ObjectAccess::getProperty($propertyValue, '_identifier', TRUE));
			}
			$this->Flow_Persistence_RelatedEntities[$originalPropertyName . '.' . $path] = array(
				'propertyName' => $originalPropertyName,
				'entityType' => $className,
				'identifier' => $identifier,
				'entityPath' => $path
			);
			$this->$originalPropertyName = \TYPO3\Flow\Utility\Arrays::setValueByPath($this->$originalPropertyName, $path, NULL);
		}
			}
}
# PathAndFilename: /var/www/html/internship-project-3-team-2/flow_login/Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Security/Cryptography/Pbkdf2HashingStrategy.php
#