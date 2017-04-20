<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Converter;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;
use Oro\Bundle\UserBundle\Entity\User;

class CustomerUserDataConverter extends ConfigurableTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function getBackendHeader()
    {
        $result = parent::getBackendHeader();

        $result[] = 'customer:name';
        $result[] = 'owner:id';

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
    protected function getHeaderForRelatedClass($entityName)
    {
        switch ($entityName) {
            case User::class:
                return [
                    ['Id' => 'id'],
                    ['Id' => 'id']
                ];
            
            case Customer::class:
                // Since Customer is a relation here, getEntityRulesAndBackendHeaders will be called recursively for it
                return [
                    ['Id' => 'id', 'Name' => 'name'],
                    ['Id' => 'id', 'Name' => 'name'],
                ];
        }
        
        return null;
    }
}
