<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Converter;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * This class is deprecated since 2.6 and will be removed.
 * @see \Oro\Bundle\CustomerBundle\ImportExport\EventListener\CustomerHeadersListener instead
 */
class CustomerDataConverter extends CommonCustomerDataConverter
{
    /**
     * {@inheritdoc}
     */
    public function getBackendHeader()
    {
        $result = parent::getBackendHeader();
        $result = $this->addOwnerId($result);
        $result = $this->addParentId($result);

        return $result;
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

    /**
     * @param array $result
     * @return array
     */
    private function addParentId(array $result)
    {
        if (!$this->fieldHelper->getConfigValue(Customer::class, 'parent', 'excluded')) {
            $result[] = 'parent:id';
        }

        return $result;
    }
}
