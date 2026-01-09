<?php

namespace Oro\Bundle\FrontendBundle\Api\Processor;

use Oro\Bundle\ApiBundle\ApiDoc\ResourceDocParserInterface;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\GetConfig\CompleteDescriptions\FieldDescriptionUtil;
use Oro\Bundle\ApiBundle\Processor\GetConfig\CompleteDescriptions\ResourceDocParserProvider;
use Oro\Bundle\ApiBundle\Processor\GetConfig\ConfigContext;
use Oro\Bundle\ApiBundle\Request\ApiAction;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\InheritDocUtil;
use Oro\Bundle\AttachmentBundle\Helper\FieldConfigHelper;
use Oro\Bundle\FrontendBundle\Api\FileFieldProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Adds human-readable descriptions for File entity related fields.
 */
class AddFileFieldDescriptions implements ProcessorInterface
{
    private const string FILE_FIELD_DOC_RESOURCE =
        '@OroFrontendBundle/Resources/doc/api_frontend/file_field.md';
    private const string FILE_TARGET_ENTITY = '%file_target_entity%';
    private const string FILE_FIELD = '%file_field%';
    private const string MULTI_FILE_FIELD = '%multi_file_field%';

    public function __construct(
        private readonly FileFieldProvider $fileFieldProvider,
        private readonly ResourceDocParserProvider $resourceDocParserProvider
    ) {
    }

    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var ConfigContext $context */

        $targetAction = $context->getTargetAction();
        if (!$targetAction || ApiAction::OPTIONS === $targetAction) {
            return;
        }

        $this->setDescriptionsForFileFields(
            $context->getResult(),
            $context->getVersion(),
            $context->getRequestType(),
            $context->getClassName(),
            $targetAction
        );
    }

    private function setDescriptionsForFileFields(
        EntityDefinitionConfig $definition,
        string $version,
        RequestType $requestType,
        string $entityClass,
        string $targetAction
    ): void {
        $fileFields = $this->fileFieldProvider->getFileFields($entityClass, $version, $requestType);
        if (!$fileFields) {
            return;
        }

        $docParser = $this->getDocumentationParser($requestType, self::FILE_FIELD_DOC_RESOURCE);
        foreach ($fileFields as $fieldName => $fieldType) {
            $fieldDefinition = $definition->getField($fieldName);
            if (null !== $fieldDefinition) {
                $isMultiFileField =
                    FieldConfigHelper::MULTI_FILE_TYPE === $fieldType
                    || FieldConfigHelper::MULTI_IMAGE_TYPE === $fieldType;
                $fieldDocumentationTemplate = $this->getFieldDocumentationTemplate(
                    $docParser,
                    self::FILE_TARGET_ENTITY,
                    $isMultiFileField ? self::MULTI_FILE_FIELD : self::FILE_FIELD,
                    $targetAction
                );
                $fieldDocumentation = InheritDocUtil::replaceInheritDoc(
                    $fieldDocumentationTemplate,
                    $definition->findFieldByPath($fieldName, true)?->getDescription()
                );
                if (
                    false === $fieldDefinition->getFormOption('mapped')
                    && (ApiAction::CREATE === $targetAction || ApiAction::UPDATE === $targetAction)
                ) {
                    $fieldDocumentation = FieldDescriptionUtil::addReadOnlyFieldNote($fieldDocumentation);
                }
                $fieldDefinition->setDescription($fieldDocumentation);
            }
        }
    }

    private function getDocumentationParser(
        RequestType $requestType,
        string $documentationResource
    ): ResourceDocParserInterface {
        $docParser = $this->resourceDocParserProvider->getResourceDocParser($requestType);
        $docParser->registerDocumentationResource($documentationResource);

        return $docParser;
    }

    private function getFieldDocumentationTemplate(
        ResourceDocParserInterface $docParser,
        string $className,
        string $fieldName,
        string $targetAction
    ): ?string {
        return $docParser->getFieldDocumentation($className, $fieldName, $targetAction)
            ?: $docParser->getFieldDocumentation($className, $fieldName);
    }
}
