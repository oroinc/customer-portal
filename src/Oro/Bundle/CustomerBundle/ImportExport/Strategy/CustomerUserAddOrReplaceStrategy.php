<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Add or replace import strategy for CustomerUser entity.
 * Handles existing entity search by case insensitive email policy in accordance with system configuration.
 */
class CustomerUserAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /** @var ConfigManager */
    private $configManager;

    /** @var bool|null */
    private $isCaseSensitiveEmailEnabled;

    /**
     * @param ConfigManager $configManager
     */
    public function setConfigManager(ConfigManager $configManager): void
    {
        $this->configManager = $configManager;
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
        if ($entity instanceof CustomerUser && $entity->isGuest()) {
            $searchContext = [$entity];
        }

        return parent::findExistingEntity($entity, $searchContext);
    }

    /**
     * {@inheritdoc}
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        return parent::findEntityByIdentityValues(
            $entityName,
            $this->handleCaseInsensitiveEmail($entityName, $identityValues)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function combineIdentityValues($entity, $entityClass, array $searchContext)
    {
        return $this->handleCaseInsensitiveEmail(
            $entityClass,
            parent::combineIdentityValues($entity, $entityClass, $searchContext)
        );
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

        if ($itemData !== null && $entitiesOfCustomerUser) {
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
    protected function getObjectValue($entity, $fieldName)
    {
        $value = parent::getObjectValue($entity, $fieldName);

        if ($fieldName === 'roles' && !$value instanceof Collection) {
            $value = new ArrayCollection($value);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateAndUpdateContext($entity)
    {
        /** @var CustomerUser $entity */
        $entity = parent::validateAndUpdateContext($entity);
        if ($entity !== null) {
            $customer = $entity->getCustomer();
            $customer->getUsers()->clear();
        }

        return $entity;
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

        if ($this->strategyHelper->checkEntityOwnerCanBeSet($entity)) {
            return $entity;
        }

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
        $userIsNotGrantedToAssign = !$this->strategyHelper->checkEntityOwnerCanBeSet($entity);

        if ($ownerChanged && $userIsNotGrantedToAssign) {
            //User tries to change owner, but has no right to change owner of CustomerUser
            //or has no access to provided user.
            return null;
        }

        return $entity;
    }

    /**
     * @param string $entityClass
     * @param array|null $identityValues
     * @return array|null
     */
    private function handleCaseInsensitiveEmail(string $entityClass, $identityValues): ?array
    {
        if (is_a($entityClass, CustomerUser::class, true) &&
            $this->isCaseInsensitiveEmailEnabled() &&
            isset($identityValues['email'])
        ) {
            $identityValues['emailLowercase'] = mb_strtolower($identityValues['email']);
            unset($identityValues['email']);
        }

        return $identityValues;
    }

    /**
     * @return bool
     */
    private function isCaseInsensitiveEmailEnabled(): bool
    {
        if ($this->isCaseSensitiveEmailEnabled === null) {
            $this->isCaseSensitiveEmailEnabled = false;

            if ($this->configManager) {
                $this->isCaseSensitiveEmailEnabled = (bool) $this->configManager
                    ->get('oro_customer.case_insensitive_email_addresses_enabled');
            }
        }

        return $this->isCaseSensitiveEmailEnabled;
    }
}
