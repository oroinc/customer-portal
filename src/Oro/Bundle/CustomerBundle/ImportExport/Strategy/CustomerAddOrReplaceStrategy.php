<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\ORM\PersistentCollection;

use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerGroup;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

class CustomerAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        $entity = parent::beforeProcessEntity($entity);
        $entity = $this->verifyIfParentExists($entity);

        return $entity;
    }

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
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = parent::findExistingEntity($entity, $searchContext);

        // we need to initialize customer groups because of issue with UoW which tries to load old data
        // from database and overrides customer groups from imported file
        if ($existingEntity instanceof CustomerGroup) {
            $customers = $existingEntity->getCustomers();
            if ($customers instanceof PersistentCollection) {
                $customers->initialize();
            }
        }

        return $existingEntity;
    }

    /**
     * @param object $entity
     * @return object|null
     */
    private function verifyIfParentExists($entity)
    {
        if ($entity instanceof Customer && $entity->getParent()) {
            $parent = $this->findExistingEntityByIdentityFields($entity->getParent());

            if ($parent === null) {
                $this->context->addPostponedRow($this->context->getValue('rawItemData'));

                return null;
            }
        }

        return $entity;
    }

    /**
     * @param $entity
     * @return Customer|null
     */
    private function verifyIfUserIsGrantedToUpdateOwner(Customer $entity = null)
    {
        if ($entity === null) {
            return null;
        }

        if ($this->isNewCustomer($entity)) {
            return $this->handleOwnerOfNewCustomer($entity);
        }

        return $this->handleOwnerOfExistingCustomer($entity);
    }
    
    /**
     * @param Customer $entity
     * @return bool
     */
    private function isNewCustomer(Customer $entity)
    {
        return $entity->getId() === null;
    }

    /**
     * @param Customer $entity
     * @return null|Customer
     */
    private function handleOwnerOfNewCustomer(Customer $entity)
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

        $this->addUnableToChangeOwnerError($entity->getName(), $entityOwner->getFullName());

        return null;
    }

    /**
     * @param Customer $entity
     * @return null|Customer
     */
    private function handleOwnerOfExistingCustomer(Customer $entity)
    {
        /** @var Customer $originalEntity */
        $originalEntity = $this->getOriginalEntityData($entity);

        $owner = $entity->getOwner();
        $ownerChanged = $owner !== null && $originalEntity['owner'] !== $owner;
        $userIsNotGrantedToAssign = !$this->strategyHelper->isGranted('ASSIGN', $entity)
            || !$this->strategyHelper->isGranted('VIEW', $owner);

        if ($ownerChanged && $userIsNotGrantedToAssign) {
            //User tries to change owner, but has no right to change owner of Customer
            //or has no access to provided user.
            $this->addUnableToChangeOwnerError($entity->getName(), $owner->getFullName());

            return null;
        }

        return $entity;
    }

    /**
     * @param Customer $entity
     * @return array
     */
    private function getOriginalEntityData(Customer $entity)
    {
        return $this->doctrineHelper->getEntityManagerForClass(Customer::class)
            ->getUnitOfWork()->getOriginalEntityData($entity);
    }

    /**
     * @param string $entityName
     * @param string $ownerName
     */
    private function addUnableToChangeOwnerError($entityName, $ownerName)
    {
        $this->context->addError(
            $this->translator->trans(
                'oro.customer.importexport.customer.unable_to_change_owner',
                [
                    '%entity_name%' => $entityName,
                    '%owner_name%' => $ownerName,
                ]
            )
        );
    }
}
