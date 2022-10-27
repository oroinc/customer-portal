<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Handles logic of preparing correct customer data for import and validating it
 */
class CustomerAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
    {
        $entity = parent::beforeProcessEntity($entity);
        $entity = $this->verifyIfParentExists($entity);
        $entity = $this->verifyIfOwnerValid($entity);

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        $entity = parent::afterProcessEntity($entity);
        $entity = $this->verifyIfUserIsGrantedToUpdateOwner($entity);

        return $entity;
    }

    /**
     * Add frontendOwner to addresses search context to prevent same addresses "stealing" by another customer.
     *
     * {@inheritdoc}
     */
    protected function generateSearchContextForRelationsUpdate($entity, $entityName, $fieldName, $isPersistRelation)
    {
        $context = parent::generateSearchContextForRelationsUpdate(
            $entity,
            $entityName,
            $fieldName,
            $isPersistRelation
        );

        if ($fieldName === 'addresses') {
            return array_merge($context, ['frontendOwner' => $entity]);
        }

        return $context;
    }

    /**
     * {@inheritdoc}
     */
    protected function findExistingEntity($entity, array $searchContext = [])
    {
        $existingEntity = parent::findExistingEntity($entity, $searchContext);

        // we need to initialize customer because of issue with UoW which tries to load old data
        // from database and overrides customer from imported file
        if ($existingEntity instanceof Customer) {
            $childrenCustomers = $existingEntity->getChildren();
            if ($childrenCustomers instanceof PersistentCollection) {
                $childrenCustomers->initialize();
            }
        }

        return $existingEntity;
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
        $entitiesOfCustomer = $entity instanceof Customer && $existingEntity instanceof Customer;

        // Configuration option ignore_empty_cells is not implemented yet, see BAP-14672 for details
        if ($itemData !== null && $entitiesOfCustomer) {
            foreach ($itemData as $fieldName => $fieldValue) {
                if ($fieldValue === null || (is_array($fieldValue) && count($fieldValue) === 0)) {
                    $excludedFields[] = $fieldName;
                }
            }
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }

    /**
     * {@inheritdoc}
     */
    protected function isPermissionGrantedForEntity($permission, $entity, $entityName)
    {
        // do not check permissions for ENUM entities
        if ($entityName === ExtendHelper::buildEnumValueClassName(Customer::INTERNAL_RATING_CODE)) {
            return true;
        }

        return parent::isPermissionGrantedForEntity($permission, $entity, $entityName);
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
                // Add validation error on the final attempt.
                if ($this->context->hasOption('attempts') && $this->context->hasOption('max_attempts')
                    && $this->context->getOption('attempts') === $this->context->getOption('max_attempts')
                ) {
                    $this->context->incrementErrorEntriesCount();
                    $this->strategyHelper->addValidationErrors(
                        [
                            $this->translator->trans(
                                'oro.customer.importexport.customer.parent_customer_not_found',
                                ['%id%' => $entity->getParent()->getId()]
                            )
                        ],
                        $this->context
                    );

                    return null;
                }

                // If there are available attempts - try to import item during the next iteration
                $this->context->addPostponedRow($this->context->getValue('rawItemData'));

                return null;
            }
        }

        return $entity;
    }

    /**
     * @param $entity
     *
     * @return null
     */
    private function verifyIfOwnerValid($entity)
    {
        if ($entity instanceof Customer && $entity->getOwner()) {
            $owner = null;

            $identifier = $this->doctrineHelper->getSingleEntityIdentifier($entity->getOwner(), false);
            if ($identifier) {
                $owner = $this->databaseHelper->find(User::class, $identifier, false);
            }

            if ($owner) {
                $entity->setOwner($owner);
                if (!$this->strategyHelper->checkEntityOwnerPermissions($this->context, $entity)) {
                    return null;
                }
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

        if ($this->strategyHelper->checkEntityOwnerPermissions($this->context, $entity, true)) {
            return $entity;
        }

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
        $userIsGrantedToAssign = $this->strategyHelper->checkEntityOwnerPermissions($this->context, $entity, true);

        if ($ownerChanged && !$userIsGrantedToAssign) {
            //User tries to change owner, but has no right to change owner of Customer
            //or has no access to provided user.
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
}
