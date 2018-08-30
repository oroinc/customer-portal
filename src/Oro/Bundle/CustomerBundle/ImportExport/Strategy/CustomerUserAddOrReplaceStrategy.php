<?php

namespace Oro\Bundle\CustomerBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
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

        $existingEntity = parent::findExistingEntity($entity, $searchContext);

        // we need to initialize customer users because of issue with UoW which tries to load old data
        // from database and overrides customer users from imported file
        if ($existingEntity instanceof Customer) {
            $customerUsers = $existingEntity->getUsers();
            if ($customerUsers instanceof PersistentCollection) {
                $customerUsers->initialize();
            }
        }

        return $existingEntity;
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
