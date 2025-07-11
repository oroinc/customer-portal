<?php

namespace Oro\Bundle\FrontendBundle\Api\Processor;

use Doctrine\Common\Collections\Criteria;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\GetConfig\ConfigContext;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Util\ConfigUtil;
use Oro\Bundle\ApiBundle\Util\EntityFieldFilteringHelper;
use Oro\Bundle\AttachmentBundle\Helper\FieldConfigHelper;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;
use Oro\Bundle\FrontendBundle\Api\FileFieldProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Adds File entity related fields to all entities that can have associations with this entity.
 */
class AddFileFields implements ProcessorInterface
{
    public function __construct(
        private readonly FileFieldProvider $fileFieldProvider,
        private readonly EntityFieldFilteringHelper $entityFieldFilteringHelper
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var ConfigContext $context */

        if (ApiAction::OPTIONS === $context->getTargetAction()) {
            return;
        }

        $entityClass = $context->getClassName();

        $fileFields = $this->fileFieldProvider->getFileFields(
            $entityClass,
            $context->getVersion(),
            $context->getRequestType()
        );
        if (!$fileFields) {
            return;
        }

        $definition = $context->getResult();
        $skipNotConfiguredCustomFields =
            $definition->getExclusionPolicy() === ConfigUtil::EXCLUSION_POLICY_CUSTOM_FIELDS
            && $this->entityFieldFilteringHelper->isExtendSystemEntity($entityClass);
        foreach ($fileFields as $fieldName => $fieldType) {
            $this->addFileField($definition, $entityClass, $fieldName, $fieldType, $skipNotConfiguredCustomFields);
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function addFileField(
        EntityDefinitionConfig $definition,
        string $entityClass,
        string $fieldName,
        string $fieldType,
        bool $skipNotConfiguredCustomFields
    ): void {
        $sourceFieldName = ConfigUtil::IGNORE_PROPERTY_PATH . $fieldName;
        if ($definition->hasField($sourceFieldName)) {
            return;
        }

        if ($skipNotConfiguredCustomFields
            && !$definition->hasField($fieldName)
            && $this->entityFieldFilteringHelper->isCustomField($entityClass, $fieldName)
        ) {
            return;
        }

        $isMultiFileField =
            FieldConfigHelper::MULTI_FILE_TYPE === $fieldType
            || FieldConfigHelper::MULTI_IMAGE_TYPE === $fieldType;

        $field = $definition->getOrAddField($fieldName);
        $field->setDataType($isMultiFileField ? DataType::OBJECTS : DataType::OBJECT);
        $field->setPropertyPath(ConfigUtil::IGNORE_PROPERTY_PATH);
        $field->setDependsOn([$fieldName . ($isMultiFileField ? '.file.id' : '.id')]);
        $field->setFormOption('mapped', false);
        $fieldTargetEntity = $field->createAndSetTargetEntity();
        $fieldTargetEntity->setExcludeAll();
        $mimeTypeProperty = $fieldTargetEntity->addField('mimeType');
        $mimeTypeProperty->setDataType(DataType::STRING);
        $mimeTypeProperty->setPropertyPath(ConfigUtil::IGNORE_PROPERTY_PATH);
        $urlProperty = $fieldTargetEntity->addField('url');
        $urlProperty->setDataType(DataType::STRING);
        $urlProperty->setPropertyPath(ConfigUtil::IGNORE_PROPERTY_PATH);

        $sourceField = $definition->addField($sourceFieldName);
        $sourceField->setPropertyPath($fieldName);
        $sourceField->setExcluded();

        if ($isMultiFileField) {
            $sourceFieldTargetEntity = $sourceField->createAndSetTargetEntity();
            $sourceFieldTargetEntity->setMaxResults(-1);
            $sourceFieldTargetEntity->setOrderBy(['sortOrder' => Criteria::ASC]);
            $definition->addField(ExtendConfigDumper::DEFAULT_PREFIX . $fieldName)
                ->setExcluded();
        }
    }
}
