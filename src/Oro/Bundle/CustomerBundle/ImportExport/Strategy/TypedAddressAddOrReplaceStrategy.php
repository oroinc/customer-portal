<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\CustomerBundle\Entity\AbstractDefaultTypedAddress;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Import-Export strategy for processing Customer User Address and Customer Address entities.
 *  - correctly handles types import with support of types removal
 *  - checks and prevent address entity "stealing"
 *  - supports Delete functionality
 */
class TypedAddressAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    protected Collection $addressTypes;
    protected bool $ownershipError = false;
    private bool $isMarkedForRemoval = false;

    /**
     * @param AbstractDefaultTypedAddress $entity
     */
    protected function beforeProcessEntity($entity)
    {
        $this->ownershipError = false;
        $this->addressTypes = new ArrayCollection();
        foreach ($entity->getAddressTypes() as $addressToType) {
            /** @var AddressType|null $addressType */
            $addressType = $this->processEntity($addressToType->getType());
            if ($addressType) {
                $addressToType->setType($addressType);
                $this->addressTypes->add($addressToType);
            }
        }
        $entity->setTypes(new ArrayCollection());

        return parent::beforeProcessEntity($entity);
    }

    /**
     * @param AbstractDefaultTypedAddress $entity
     */
    protected function afterProcessEntity($entity)
    {
        $itemData = $this->context->getValue('itemData');
        $this->isMarkedForRemoval = false;
        // Do not create new entity if it marked for removal
        if (!empty($itemData['Delete'])) {
            if (!$entity->getId()) {
                return null;
            }

            $this->isMarkedForRemoval = true;
            $markedForRemoval = (array)$this->context->getValue('marked_for_removal');
            $markedForRemoval[] = $entity->getId();
            $this->context->setValue('marked_for_removal', $markedForRemoval);
        }

        $entity->setAddressTypes($this->addressTypes);

        return parent::afterProcessEntity($entity);
    }

    protected function updateContextCounters($entity)
    {
        if ($this->isMarkedForRemoval) {
            $this->context->incrementDeleteCount();
        } else {
            parent::updateContextCounters($entity);
        }
    }

    protected function validateAndUpdateContext($entity)
    {
        if ($this->ownershipError) {
            $this->processValidationErrors($entity, ['The owner of the existing address should not be changed']);

            return null;
        }

        return parent::validateAndUpdateContext($entity);
    }

    protected function checkEntityAcl($entity, $existingEntity = null, $itemData = null)
    {
        // Set systemOrganization same to owner if none set.
        if ($entity instanceof AbstractDefaultTypedAddress && !$entity->getSystemOrganization()) {
            $entity->setSystemOrganization(
                $entity->getFrontendOwner()->getOrganization()
            );
        }

        parent::checkEntityAcl($entity, $existingEntity, $itemData);
    }

    protected function importExistingEntity($entity, $existingEntity, $itemData = null, array $excludedFields = [])
    {
        if ($entity instanceof AbstractDefaultTypedAddress) {
            if ($existingEntity->getId()) {
                if ($existingEntity->getFrontendOwner()?->getId() !== $entity->getFrontendOwner()?->getId()) {
                    $this->ownershipError = true;
                }
                // Do not allow to override the owner.
                $excludedFields[] = 'frontendOwner';
            }
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }
}
