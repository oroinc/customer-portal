<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class CustomerUserAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function importExistingEntity(
        $entity,
        $existingEntity,
        $itemData = null,
        array $excludedFields = []
    ) {
        $entitiesOfCustomerUser = $entity instanceof CustomerUser && $existingEntity instanceof CustomerUser;

        // As email is identity we need to manually exclude it in case it's empty. More info: BB-7978
        if ($entitiesOfCustomerUser && $entity->getEmail() === null) {
            $excludedFields[] = 'email';
        }

        if ($entitiesOfCustomerUser && count($itemData['roles']) === 0) {
            $excludedFields[] = 'roles';
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }

    /**
     * {@inheritdoc}
     * @param CustomerUser $entity
     */
    public function validateAndUpdateContext($entity)
    {
        $validationErrors = $this->strategyHelper->validateEntity($entity, ['Default', 'import']);
        if ($validationErrors) {
            $this->processValidationErrors($entity, $validationErrors);

            return null;
        }

        $this->updateContextCounters($entity);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function getObjectValue($entity, $fieldName)
    {
        $value = parent::getObjectValue($entity, $fieldName);

        if ($fieldName === 'roles' && !$value instanceof Collection) {
            $value = new ArrayCollection($value);
        }

        return $value;
    }
}
