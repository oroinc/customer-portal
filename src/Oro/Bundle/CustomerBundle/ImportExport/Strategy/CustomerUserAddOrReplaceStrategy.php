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
    public function afterProcessEntity($entity)
    {
        $entity = parent::afterProcessEntity($entity);
        $entity = $this->verifyIfUserIsGrantedToUpdateOwner($entity);

        return $entity;
    }

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
    
    /**
     * @param $entity
     * @return CustomerUser|null
     */
    private function verifyIfUserIsGrantedToUpdateOwner(CustomerUser $entity = null)
    {
        if ($entity === null) {
            return null;
        }

        if ($this->isNewCustomerUser($entity)) {
            return $this->handleOwnerOfNewCustomerUser($entity);
        }
        
        return $this->handleOwnerOfExistingCustomerUser($entity);
    }

    /**
     * @param CustomerUser $entity
     * @return bool
     */
    private function isNewCustomerUser(CustomerUser $entity)
    {
        return $entity->getId() === null;
    }

    /**
     * @param CustomerUser $entity
     * @return array
     */
    private function getOriginalEntityData(CustomerUser $entity)
    {
        return $this->doctrineHelper->getEntityManagerForClass(CustomerUser::class)
            ->getUnitOfWork()->getOriginalEntityData($entity);
    }

    /**
     * @param CustomerUser $entity
     * @return null|CustomerUser
     */
    private function handleOwnerOfNewCustomerUser(CustomerUser $entity)
    {
        $entityOwner = $entity->getOwner();

        if ($entityOwner === null) {
            $entity->setOwner($this->strategyHelper->getLoggedUser());

            return $entity;
        }

        if ($entityOwner === $this->strategyHelper->getLoggedUser()) {
            return $entity;
        }

        if ($this->strategyHelper->isGranted('ASSIGN', $entity)
            && $this->strategyHelper->isGranted('VIEW', $entityOwner)
        ) {
            return $entity;
        }

        $this->addUnableToChangeOwnerError($entity->getFullName(), $entityOwner->getFullName());

        return null;
    }

    /**
     * @param CustomerUser $entity
     * @return null|CustomerUser
     */
    private function handleOwnerOfExistingCustomerUser(CustomerUser $entity)
    {
        /** @var CustomerUser $originalEntity */
        $originalEntity = $this->getOriginalEntityData($entity);

        $owner = $entity->getOwner();
        $ownerChanged = $owner !== null && $originalEntity['owner'] !== $owner;
        $userIsNotGrantedToAssign = !$this->strategyHelper->isGranted('ASSIGN', $entity)
            || !$this->strategyHelper->isGranted('VIEW', $owner);

        if ($ownerChanged && $userIsNotGrantedToAssign) {
            //User tries to change owner, but has no right to change owner of CustomerUser
            //or has no access to provided user.
            $this->addUnableToChangeOwnerError($entity->getFullName(), $owner->getFullName());

            return null;
        }

        return $entity;
    }

    /**
     * @param string $entityName
     * @param string $ownerName
     */
    private function addUnableToChangeOwnerError($entityName, $ownerName)
    {
        $this->context->addError(
            $this->translator->trans(
                'oro.customer.importexport.customer_user.unable_to_change_owner',
                [
                    '%entity_name%' => $entityName,
                    '%owner_name%' => $ownerName,
                ]
            )
        );
    }
}
