<?php 
namespace TYPO3\Flow\Property\TypeConverter;

/*                                                                        *
 * This script belongs to the TYPO3 Flow framework.                       *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Property\Exception\InvalidPropertyMappingConfigurationException;
use TYPO3\Flow\Property\Exception\InvalidSourceException;
use TYPO3\Flow\Property\PropertyMappingConfigurationInterface;

/**
 * Converter which transforms various types to arrays.
 *
 * * If the source is an array, it is returned unchanged.
 * * If the source is a string, is is converted depending on CONFIGURATION_STRING_FORMAT,
 *   which can be STRING_FORMAT_CSV or STRING_FORMAT_JSON. For CSV the delimiter can be
 *   set via CONFIGURATION_STRING_DELIMITER.
 * * If the source is a Resource object, it is converted to an array. The actual resource
 *   content is either embedded as base64-encoded data or saved to a file, depending on
 *   CONFIGURATION_RESOURCE_EXPORT_TYPE. For RESOURCE_EXPORT_TYPE_FILE the setting
 *   CONFIGURATION_RESOURCE_SAVE_PATH must be set as well.
 *
 * @api
 * @Flow\Scope("singleton")
 */
class ArrayConverter_Original extends AbstractTypeConverter {

	/**
	 * @var string
	 */
	const CONFIGURATION_STRING_DELIMITER = 'stringDelimiter';

	/**
	 * @var string
	 */
	const DEFAULT_STRING_DELIMITER = ',';

	/**
	 * @var string
	 */
	const CONFIGURATION_STRING_FORMAT = 'stringFormat';

	/**
	 * @var string
	 */
	const DEFAULT_STRING_FORMAT = self::STRING_FORMAT_CSV;

	/**
	 * @var string
	 */
	const STRING_FORMAT_CSV = 'csv';

	/**
	 * @var string
	 */
	const STRING_FORMAT_JSON = 'json';

	/**
	 * @var string
	 */
	const CONFIGURATION_RESOURCE_EXPORT_TYPE = 'resourceExportType';

	/**
	 * @var string
	 */
	const DEFAULT_RESOURCE_EXPORT_TYPE = self::RESOURCE_EXPORT_TYPE_BASE64;

	/**
	 * @var string
	 */
	const RESOURCE_EXPORT_TYPE_BASE64 = 'base64';

	/**
	 * @var string
	 */
	const RESOURCE_EXPORT_TYPE_FILE = 'file';

	/**
	 * @var string
	 */
	const CONFIGURATION_RESOURCE_SAVE_PATH = 'resourceSavePath';

	/**
	 * @var array<string>
	 */
	protected $sourceTypes = array('array', 'string', 'TYPO3\Flow\Resource\Resource');

	/**
	 * @var string
	 */
	protected $targetType = 'array';

	/**
	 * @var integer
	 */
	protected $priority = 1;

	/**
	 * Convert from $source to $targetType, a noop if the source is an array.
	 *
	 * If it is a string it will be converted according to the configured string format.
	 *
	 * @param mixed $source
	 * @param string $targetType
	 * @param array $convertedChildProperties
	 * @param PropertyMappingConfigurationInterface $configuration
	 * @return array
	 * @api
	 */
	public function convertFrom($source, $targetType, array $convertedChildProperties = array(), PropertyMappingConfigurationInterface $configuration = NULL) {
		if (is_array($source)) {
			return $source;
		}

		if (is_string($source)) {
			if ($source === '') {
				return array();
			} else {
				$stringFormat = $this->getStringFormat($configuration);
				switch ($stringFormat) {
					case self::STRING_FORMAT_CSV:
						return explode($this->getStringDelimiter($configuration), $source);
					case self::STRING_FORMAT_JSON:
						return json_decode($source, TRUE);
					default:
						throw new InvalidPropertyMappingConfigurationException(sprintf('Conversion from string to array failed due to invalid string format setting "%s"', $stringFormat), 1404903208);
				}
			}
		}

		if ($source instanceof \TYPO3\Flow\Resource\Resource) {
			$exportType = $this->getResourceExportType($configuration);
			switch ($exportType) {
				case self::RESOURCE_EXPORT_TYPE_BASE64:
					return array(
						'filename' => $source->getFilename(),
						'data' => base64_encode(file_get_contents('resource://' . $source->getSha1()))
					);
				case self::RESOURCE_EXPORT_TYPE_FILE:
					$sourceStream = $source->getStream();
					if ($sourceStream === FALSE) {
						throw new InvalidSourceException(sprintf('Could not get stream of resource "%s" (%s). This might be caused by a broken resource object and can be fixed by running the "resource:clean" command.', $source->getFilename(), $source->getSha1()), 1435842312);
					}
					$targetStream = fopen($configuration->getConfigurationValue('TYPO3\Flow\Property\TypeConverter\ArrayConverter', self::CONFIGURATION_RESOURCE_SAVE_PATH) . '/' . $source->getSha1(), 'w');
					stream_copy_to_stream($sourceStream, $targetStream);
					fclose($targetStream);
					fclose($sourceStream);
					return array(
						'filename' => $source->getFilename(),
						'hash' => $source->getSha1(),
					);
				default:
					throw new InvalidPropertyMappingConfigurationException(sprintf('Conversion from Resource to array failed due to invalid resource export type setting "%s"', $exportType), 1404903210);

			}
		}

		throw new \TYPO3\Flow\Property\Exception\TypeConverterException('Conversion to array failed for unknown reason', 1404903387);
	}

	/**
	 * @param PropertyMappingConfigurationInterface $configuration
	 * @return string
	 * @throws InvalidPropertyMappingConfigurationException
	 */
	protected function getStringDelimiter(PropertyMappingConfigurationInterface $configuration = NULL) {
		if ($configuration === NULL) {
			return self::DEFAULT_STRING_DELIMITER;
		}

		$stringDelimiter = $configuration->getConfigurationValue('TYPO3\Flow\Property\TypeConverter\ArrayConverter', self::CONFIGURATION_STRING_DELIMITER);
		if ($stringDelimiter === NULL) {
			return self::DEFAULT_STRING_DELIMITER;
		} elseif (!is_string($stringDelimiter)) {
			throw new InvalidPropertyMappingConfigurationException(sprintf('CONFIGURATION_STRING_DELIMITER must be of type string, "%s" given', (is_object($stringDelimiter) ? get_class($stringDelimiter) : gettype($stringDelimiter))), 1368433339);
		}

		return $stringDelimiter;
	}

	/**
	 * @param PropertyMappingConfigurationInterface $configuration
	 * @return string
	 * @throws InvalidPropertyMappingConfigurationException
	 */
	protected function getStringFormat(PropertyMappingConfigurationInterface $configuration = NULL) {
		if ($configuration === NULL) {
			return self::DEFAULT_STRING_FORMAT;
		}

		$stringFormat = $configuration->getConfigurationValue('TYPO3\Flow\Property\TypeConverter\ArrayConverter', self::CONFIGURATION_STRING_FORMAT);
		if ($stringFormat === NULL) {
			return self::DEFAULT_STRING_FORMAT;
		} elseif (!is_string($stringFormat)) {
			throw new InvalidPropertyMappingConfigurationException(sprintf('CONFIGURATION_STRING_FORMAT must be of type string, "%s" given', (is_object($stringFormat) ? get_class($stringFormat) : gettype($stringFormat))), 1404227443);
		}

		return $stringFormat;
	}

	/**
	 * @param PropertyMappingConfigurationInterface $configuration
	 * @return string
	 * @throws InvalidPropertyMappingConfigurationException
	 */
	protected function getResourceExportType(PropertyMappingConfigurationInterface $configuration = NULL) {
		if ($configuration === NULL) {
			return self::DEFAULT_RESOURCE_EXPORT_TYPE;
		}

		$exportType = $configuration->getConfigurationValue('TYPO3\Flow\Property\TypeConverter\ArrayConverter', self::CONFIGURATION_RESOURCE_EXPORT_TYPE);
		if ($exportType === NULL) {
			return self::DEFAULT_RESOURCE_EXPORT_TYPE;
		} elseif (!is_string($exportType)) {
			throw new InvalidPropertyMappingConfigurationException(sprintf('RESOURCE_EXPORT_TYPE must be of type string, "%s" given', (is_object($exportType) ? get_class($exportType) : gettype($exportType))), 1404313373);
		}

		return $exportType;
	}
}
namespace TYPO3\Flow\Property\TypeConverter;

use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Annotations as Flow;

/**
 * Converter which transforms various types to arrays.
 * 
 * * If the source is an array, it is returned unchanged.
 * * If the source is a string, is is converted depending on CONFIGURATION_STRING_FORMAT,
 *   which can be STRING_FORMAT_CSV or STRING_FORMAT_JSON. For CSV the delimiter can be
 *   set via CONFIGURATION_STRING_DELIMITER.
 * * If the source is a Resource object, it is converted to an array. The actual resource
 *   content is either embedded as base64-encoded data or saved to a file, depending on
 *   CONFIGURATION_RESOURCE_EXPORT_TYPE. For RESOURCE_EXPORT_TYPE_FILE the setting
 *   CONFIGURATION_RESOURCE_SAVE_PATH must be set as well.
 * @\TYPO3\Flow\Annotations\Scope("singleton")
 */
class ArrayConverter extends ArrayConverter_Original implements \TYPO3\Flow\Object\Proxy\ProxyInterface {


	/**
	 * Autogenerated Proxy Method
	 */
	public function __construct() {
		if (get_class($this) === 'TYPO3\Flow\Property\TypeConverter\ArrayConverter') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Property\TypeConverter\ArrayConverter', $this);
	}

	/**
	 * Autogenerated Proxy Method
	 */
	 public function __wakeup() {
		if (get_class($this) === 'TYPO3\Flow\Property\TypeConverter\ArrayConverter') \TYPO3\Flow\Core\Bootstrap::$staticObjectManager->setInstance('TYPO3\Flow\Property\TypeConverter\ArrayConverter', $this);

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
	$reflectedClass = new \ReflectionClass('TYPO3\Flow\Property\TypeConverter\ArrayConverter');
	$allReflectedProperties = $reflectedClass->getProperties();
	foreach ($allReflectedProperties as $reflectionProperty) {
		$propertyName = $reflectionProperty->name;
		if (in_array($propertyName, array('Flow_Aop_Proxy_targetMethodsAndGroupedAdvices', 'Flow_Aop_Proxy_groupedAdviceChains', 'Flow_Aop_Proxy_methodIsInAdviceMode'))) continue;
		if (isset($this->Flow_Injected_Properties) && is_array($this->Flow_Injected_Properties) && in_array($propertyName, $this->Flow_Injected_Properties)) continue;
		if ($reflectionProperty->isStatic() || $reflectionService->isPropertyAnnotatedWith('TYPO3\Flow\Property\TypeConverter\ArrayConverter', $propertyName, 'TYPO3\Flow\Annotations\Transient')) continue;
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
				$varTagValues = $reflectionService->getPropertyTagValues('TYPO3\Flow\Property\TypeConverter\ArrayConverter', $propertyName, 'var');
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
# PathAndFilename: /var/www/html/flow-login/Packages/Framework/TYPO3.Flow/Classes/TYPO3/Flow/Property/TypeConverter/ArrayConverter.php
#