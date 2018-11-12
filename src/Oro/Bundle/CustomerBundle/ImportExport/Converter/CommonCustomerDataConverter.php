<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;

/**
 * This class is deprecated since 2.6 and will be removed.
 * @see \Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerHeadersListener
 * and \Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerUserHeadersListener instead
 */
abstract class CommonCustomerDataConverter extends ConfigurableTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    protected function getEntityRules(
        $entityName,
        $fullData = false,
        $singleRelationDeepLevel = 0,
        $multipleRelationDeepLevel = 0
    ) {
        if (!$fullData) {
            $header = $this->getHeaderForRelatedClass($entityName);
            if ($header) {
                // 0 -> rules, 1 -> backend headers
                return $header[0];
            }
        }

        return parent::getEntityRules(
            $entityName,
            $fullData,
            $singleRelationDeepLevel,
            $multipleRelationDeepLevel
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityRulesAndBackendHeaders(
        $entityName,
        $fullData = false,
        $singleRelationDeepLevel = 0,
        $multipleRelationDeepLevel = 0
    ) {
        if (!$fullData) {
            $header = $this->getHeaderForRelatedClass($entityName);
            if ($header) {
                return $header;
            }
        }

        return parent::getEntityRulesAndBackendHeaders(
            $entityName,
            $fullData,
            $singleRelationDeepLevel,
            $multipleRelationDeepLevel
        );
    }

    /**
     * @param string $entityName
     * @return array
     */
    abstract protected function getHeaderForRelatedClass($entityName);
}
