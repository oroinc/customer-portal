<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Converter;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;

class CustomerUserDataConverter extends ConfigurableTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function getBackendHeader()
    {
        $result = parent::getBackendHeader();

        $result[] = 'customer:name';

        return $result;
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
        if ($entityName === Customer::class && !$fullData) {
            // Since Customer is a relation here, getEntityRulesAndBackendHeaders will be called recursively for it
            return [
                ['Id' => 'id', 'Name' => 'name'],
                ['Id' => 'id', 'Name' => 'name'],
            ];
        }

        return parent::getEntityRulesAndBackendHeaders(
            $entityName,
            $fullData,
            $singleRelationDeepLevel,
            $multipleRelationDeepLevel
        );
    }
}
