<?php

namespace Oro\Bundle\FrontendBundle\Api\Processor;

use Doctrine\ORM\Query;
use Oro\Bundle\ApiBundle\Config\EntityDefinitionConfig;
use Oro\Bundle\ApiBundle\Processor\CustomizeLoadedData\CustomizeLoadedDataContext;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Util\ConfigUtil;
use Oro\Bundle\ApiBundle\Util\DoctrineHelper;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\AttachmentBundle\Helper\FieldConfigHelper;
use Oro\Bundle\AttachmentBundle\Manager\AttachmentManager;
use Oro\Bundle\FrontendBundle\Api\FileFieldProvider;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Computes values of File entity related fields.
 */
class ComputeFileFields implements ProcessorInterface
{
    public function __construct(
        private readonly FileFieldProvider $fileFieldProvider,
        private readonly AttachmentManager $attachmentManager,
        private readonly DoctrineHelper $doctrineHelper
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    #[\Override]
    public function process(ContextInterface $context): void
    {
        /** @var CustomizeLoadedDataContext $context */

        $fileFields = $this->getFileFields(
            $context->getClassName(),
            $context->getVersion(),
            $context->getRequestType(),
            $context->getConfig()
        );
        if (!$fileFields) {
            return;
        }

        $data = $context->getData();
        $this->preloadFiles($data, $fileFields);
        foreach ($fileFields as $fieldName => $fieldType) {
            foreach ($data as $key => $item) {
                $srcFieldName = ConfigUtil::IGNORE_PROPERTY_PATH . $fieldName;
                if (!\array_key_exists($srcFieldName, $item)) {
                    continue;
                }
                if ($this->isMultiFileField($fieldType)) {
                    $fieldData = [];
                    foreach ($item[$srcFieldName] as $srcFieldDataItem) {
                        /** @var File|null $file */
                        $file = $srcFieldDataItem['file'];
                        if (null !== $file) {
                            $fieldData[] = $this->getFileInfo($file);
                        }
                    }
                    $data[$key][$fieldName] = $fieldData;
                } else {
                    $data[$key][$fieldName] = $item[$srcFieldName] ? $this->getFileInfo($item[$srcFieldName]) : null;
                }
            }
        }
        $context->setData($data);
    }

    private function getFileInfo(File $file): array
    {
        return [
            'mimeType' => $file->getMimeType(),
            'url' => $this->attachmentManager->getFileUrl($file)
        ];
    }

    private function getFileFields(
        string $entityClass,
        string $version,
        RequestType $requestType,
        ?EntityDefinitionConfig $config
    ): array {
        $fileFields = $this->fileFieldProvider->getFileFields($entityClass, $version, $requestType);
        if ($fileFields && null !== $config) {
            $filteredFileFields = [];
            foreach ($fileFields as $fieldName => $fieldType) {
                $fieldConfig = $config->getField($fieldName);
                if (null !== $fieldConfig && !$fieldConfig->isExcluded()) {
                    $filteredFileFields[$fieldName] = $fieldType;
                }
            }
            $fileFields = $filteredFileFields;
        }

        return $fileFields;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function preloadFiles(array &$data, array $fileFields): void
    {
        $fileIds = [];
        foreach ($fileFields as $fieldName => $fieldType) {
            foreach ($data as $item) {
                $srcFieldData = $item[ConfigUtil::IGNORE_PROPERTY_PATH . $fieldName] ?? null;
                if ($srcFieldData) {
                    if ($this->isMultiFileField($fieldType)) {
                        foreach ($srcFieldData as $srcFieldDataItem) {
                            $fileIds[] = $srcFieldDataItem['file']['id'];
                        }
                    } else {
                        $fileIds[] = $srcFieldData['id'];
                    }
                }
            }
        }
        if (!$fileIds) {
            return;
        }

        $fileMap = [];
        $files = $this->doctrineHelper->createQueryBuilder(File::class, 'e')
            ->where('e.id IN (:ids)')
            ->setParameter('ids', $fileIds)
            ->getQuery()
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult();
        foreach ($files as $file) {
            $fileMap[$file->getId()] = $file;
        }

        foreach ($fileFields as $fieldName => $fieldType) {
            foreach ($data as $key => $item) {
                $srcFieldName = ConfigUtil::IGNORE_PROPERTY_PATH . $fieldName;
                $srcFieldData = $item[$srcFieldName] ?? null;
                if ($srcFieldData) {
                    if ($this->isMultiFileField($fieldType)) {
                        foreach ($srcFieldData as $i => $srcFieldDataItem) {
                            $data[$key][$srcFieldName][$i]['file'] = $fileMap[$srcFieldDataItem['file']['id']] ?? null;
                        }
                    } else {
                        $data[$key][$srcFieldName] = $fileMap[$srcFieldData['id']] ?? null;
                    }
                }
            }
        }
    }

    private function isMultiFileField(string $fieldType): bool
    {
        return
            FieldConfigHelper::MULTI_FILE_TYPE === $fieldType
            || FieldConfigHelper::MULTI_IMAGE_TYPE === $fieldType;
    }
}
