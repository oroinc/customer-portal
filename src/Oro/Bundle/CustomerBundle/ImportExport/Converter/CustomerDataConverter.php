<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Converter;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\ImportExportBundle\Converter\ConfigurableTableDataConverter;
use Oro\Bundle\UserBundle\Entity\User;

class CustomerDataConverter extends ConfigurableTableDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function getBackendHeader()
    {
        $result = parent::getBackendHeader();
        $result = $this->addOwnerId($result);

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
        }

        return null;
    }

    /**
     * @param array $result
     * @return array
     */
    private function addOwnerId(array $result)
    {
        if (!$this->fieldHelper->getConfigValue(Customer::class, 'owner', 'excluded')) {
            $result[] = 'owner:id';
        }

        return $result;
    }
}
