<?php

namespace Oro\Bundle\FrontendBundle\Api;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\AttachmentBundle\Helper\FieldConfigHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Provides information about File entity related fields.
 */
class FileFieldProvider implements ResetInterface
{
    private const KEY_DELIMITER = '|';

    private array $fileFields = [];

    public function __construct(
        private readonly DoctrineHelper $doctrineHelper,
        private readonly ConfigManager $configManager
    ) {
    }

    #[\Override]
    public function reset(): void
    {
        $this->fileFields = [];
    }

    /**
     * @return array [field name => field type, ...]
     */
    public function getFileFields(
        string $entityClass,
        string $version,
        RequestType $requestType
    ): array {
        $cacheKey = (string)$requestType . self::KEY_DELIMITER . $version . self::KEY_DELIMITER . $entityClass;
        if (\array_key_exists($cacheKey, $this->fileFields)) {
            return $this->fileFields[$cacheKey];
        }

        $fileFields = [];
        if (
            $this->doctrineHelper->isManageableEntityClass($entityClass)
            && $this->configManager->hasConfig($entityClass)
        ) {
            $fieldConfigs = $this->configManager->getConfigs('extend', $entityClass);
            foreach ($fieldConfigs as $fieldConfig) {
                $fieldConfigId = $fieldConfig->getId();
                if (
                    (FieldConfigHelper::isFileField($fieldConfigId) || FieldConfigHelper::isImageField($fieldConfigId))
                    && ExtendHelper::isFieldAccessible($fieldConfig)
                ) {
                    $fileFields[$fieldConfigId->getFieldName()] = $fieldConfigId->getFieldType();
                }
            }
        }
        $this->fileFields[$cacheKey] = $fileFields;

        return $fileFields;
    }
}
